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

    public function __construct($businessTrips, $caData)
    {
        $this->businessTrips = $businessTrips;
        $this->caData = $caData;
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

        return [
            $businessTrip->jns_dinas,
            $businessTrip->nama,
            $businessTrip->divisi,
            $businessTrip->no_sppd,
            Carbon::parse($businessTrip->mulai)->format('d-m-Y'),
            Carbon::parse($businessTrip->kembali)->format('d-m-Y'),
            $businessTrip->tujuan,
            $businessTrip->bb_perusahaan,
            $totalCA !== null ? $totalCA : '-',
            $totalReal !== null ? $totalReal : '-',
            $totalCost !== null ? $totalCost : '-',
            $businessTrip->created_at,
            $businessTrip->tanggal_diterima_hrd ?? '-',
            $businessTrip->tanggal_diproses_hrd ?? '-',
            $businessTrip->tanggal_penyerahan_ke ?? '-',
            $relatedCA ? $relatedCA->total_days . " Days" : '-', // Example field
            // $businessTrip->hari_berjalan,
        ];
    }


    public function headings(): array
    {
        return [
            'Jenis Perjalanan',
            'Nama',
            'Departemen',
            'No SPPD',
            'Mulai',
            'Kembali',
            'Tujuan',
            'PT',
            'Uang Muka',
            'Realisasi',
            'Sisa/Kurang',
            'Tanggal permintaan nomor',
            'Tanggal diterima HRD',
            'Tanggal diproses HRD',
            'Tanggal penyerahan ke',
            'Hari Berjalan',
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
        $sheet->getStyle('A1:P1')->applyFromArray([
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
