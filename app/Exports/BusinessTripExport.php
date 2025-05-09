<?php

namespace App\Exports;

use App\Models\BusinessTrip;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class BusinessTripExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings, WithStyles
{
    use Exportable;

    protected $businessTrips;
    protected $caData;
    protected $btApprovalData;

    public function __construct($businessTrips, $caData, $btApprovalData)
    {
        $this->businessTrips = $businessTrips;
        $this->caData = $caData;
        $this->btApprovalData = $btApprovalData;
    }

    public function collection()
    {
        return $this->businessTrips;
    }

    public function map($businessTrip): array
    {
        // Find related CA data for this BusinessTrip
        $relatedCA = $this->caData->firstWhere('no_sppd', $businessTrip->no_sppd);
        $totalCA = $relatedCA ? $relatedCA->total_ca : 0;
        $totalReal = $relatedCA ? $relatedCA->total_real : 0;
        $totalCost = $relatedCA ? $relatedCA->total_cost : 0;
        $declarationAt = $relatedCA ? Carbon::parse($relatedCA->declaration_at)->format('d-m-Y') : "-";
        
        $status = $businessTrip->status;

        $approvedReqStatuses = [
            'Approved','Declaration Draft','Declaration L1','Declaration L2','Declaration Approved','Declaration Rejected','Declaration Revision','Doc Accepted','Return/Refund','Verified','Extend L2'
        ];

        if (in_array($status, $approvedReqStatuses)) {
            $displayReqStatus = 'Approved';
        } else {
            $displayReqStatus = $status;
        }

        $approvedDecStatuses = [
            'Declaration Approved','Doc Accepted','Return/Refund','Verified','Extend L2'
        ];

        $pendingDecStatuses = [
            'Declaration L1','Declaration L2','Declaration Rejected','Declaration Revision'
        ];

        if (in_array($status, $approvedDecStatuses)) {
            $displayDecStatus = 'Approved';
        } else if (in_array($status, $pendingDecStatuses)){
            $displayDecStatus = $status;
        } else {
            $displayDecStatus = '-';
        }

        $displayDecStatus = $relatedCA ? $displayDecStatus : "-";

        $adminStatuses = [
            'Doc Accepted','Return/Refund','Verified'
        ];

        if (in_array($status, $adminStatuses)) {
            $displayAdminStatus = $status;
            if($displayAdminStatus=='Doc Accepted'){
                $dateAdminStatus = Carbon::parse($businessTrip->doc_at)->format('d-m-Y');
            }else if($displayAdminStatus=='Return/Refund'){
                $dateAdminStatus = Carbon::parse($businessTrip->return_at)->format('d-m-Y');
            }else if($displayAdminStatus=='Verified'){
                $dateAdminStatus = Carbon::parse($businessTrip->verified_at)->format('d-m-Y');
            }            
        } else {
            $displayAdminStatus = '-';
            $dateAdminStatus = '-';
        }

        $relatedBtApproval = $this->btApprovalData->firstWhere('bt_id', $businessTrip->id);
        $doc_at = $businessTrip->doc_at ? Carbon::parse($businessTrip->doc_at)->format('d-m-Y') : '-';
        $return_at = $businessTrip->return_at ? Carbon::parse($businessTrip->return_at)->format('d-m-Y') : '-';
        $verified_at = $businessTrip->verified_at ? Carbon::parse($businessTrip->verified_at)->format('d-m-Y') : '-';
        
        return [
            $businessTrip->employee->employee_id,
            $businessTrip->nama,
            $businessTrip->employee->designation_name,
            $businessTrip->divisi,
            $businessTrip->employee->group_company,
            $businessTrip->jns_dinas,
            $businessTrip->checkCompany->contribution_level,
            $businessTrip->no_sppd,
            Carbon::parse($businessTrip->created_date)->format('d-m-Y'), //Request Date (tgl karyawan submit request travel)
            $displayReqStatus, //Request Status (pilihan : approved/ pending L1/ pending L2/ etc)
            $declarationAt, //Declaration Date (tgl karyawan submit declaration)
            $displayDecStatus, //Declaration Status (pilihan : approved/ pending L1/ pending L2/ etc)
            Carbon::parse($businessTrip->mulai)->format('d-m-Y'), //Mulai (tgl karyawan mulai dinas)
            Carbon::parse($businessTrip->kembali)->format('d-m-Y'), //Kembali (tgl kayawa selesai dinas)
            $businessTrip->tujuan, //Tujuan (ke mana)
            $totalCA !== null ? $totalCA : '-', //Uang Muka (Uang CA)
            $totalReal !== null ? $totalReal : '-', //Realisasi (Uang actual terpakai)
            $totalCost !== null ? $totalCost : '-', //Sisa/Kurang
            Carbon::parse($businessTrip->created_at)->format('d-m-Y'), //Tanggal permintaan nomor SPPD
            $doc_at, //Tanggal diterima HRD (tgl deklarasi diterima HRD)
            $return_at, //Tanggal diproses HRD (tgl HRD verifikasi deklarasi)
            $verified_at, //Tanggal penyerahan ke Accounting
            $displayAdminStatus, //Status (status deklarasi, pilihannya : done/reject/refund or refund)
            $dateAdminStatus, //Status Date (tgl status tersebut muncul/berubah)
        ];
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama',
            'Designation',
            'Departemen',
            'BU',
            'Jenis Perjalanan',
            'PT',
            'No SPPD',
            'Request Date',
            'Request Status',
            'Declaration Date',
            'Declaration Status',
            'Mulai',
            'Kembali',
            'Tujuan',
            'Uang Muka',
            'Realisasi',
            'Sisa/Kurang',
            'Tanggal permintaan nomor',
            'Tanggal diterima HRD',
            'Tanggal diproses HRD',
            'Tanggal penyerahan ke',
            'Status',
            'Status Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $currencyStyle = [
            'numberFormat' => [
                'formatCode' => 'Rp #,##0' // Custom format for IDR
            ]
        ];

        // Apply styles to specific columns (update column letters as needed)
        $sheet->getStyle('I2:I' . ($sheet->getHighestRow()))->applyFromArray($currencyStyle); // For total_ca
        $sheet->getStyle('J2:J' . ($sheet->getHighestRow()))->applyFromArray($currencyStyle); // For total_real
        $sheet->getStyle('K2:K' . ($sheet->getHighestRow()))->applyFromArray($currencyStyle); // For total_cost

        // Apply styles to headers at A1:P1
        $sheet->getStyle('A1:X1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFCCFFCC', // Light green color
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'], // Black border color
                ],
            ],
        ]);

        // Set column widths
        foreach (range('B', 'Q') as $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth(20);
        }
    }


}
