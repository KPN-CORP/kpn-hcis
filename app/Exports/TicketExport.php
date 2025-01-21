<?php

namespace App\Exports;

use App\Models\Tiket;
use App\Models\TiketApproval;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class TicketExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $tktType;

    public function __construct($startDate, $endDate, $tktType)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tktType = $tktType;
    }

    public function collection()
    {
        $query = Tiket::where('approval_status', '!=', 'Draft');  

        // Add date filters if they are provided (not null)  
        if ($this->startDate && $this->endDate) {  
            $query->whereBetween('tgl_brkt_tkt', [$this->startDate, $this->endDate]);  
        }  

        // Add ticket type filter if it's provided (not the default value '-')  
        if ($this->tktType && $this->tktType !== '-') {  
            $query->where('jns_dinas_tkt', $this->tktType);  
        }  

        // Execute the query and group by ticket number  
        $ticketData = $query->get()->groupBy('no_tkt');  

        $combinedData = [];
        $index = 1;

        foreach ($ticketData as $noTiket => $item) {
            foreach ($item as $items) {
                $employee = Employee::where('id', $items->user_id)->first();
                if ($items->approval_status == 'Approved' || $items->approval_status == 'Rejected') {
                    $approval = TiketApproval::where('tkt_id', $items->id)->orderBy('layer', 'desc')->first();
                    if ($approval) {
                        $manager = Employee::where('employee_id', $approval->employee_id)->first();
                    }
                } elseif ($items->approval_status == 'Pending L1') {
                    $manager = Employee::where('employee_id', $employee->manager_l1_id)->first();
                } elseif ($items->approval_status == 'Pending L2') {
                    $manager = Employee::where('employee_id', $employee->manager_l2_id)->first();
                }
                $combinedData[] = [
                    'number' => $index,
                    'User' => $employee->fullname,
                    'Atasan' => $manager ? $manager->fullname : 'Unknown',
                    'Status' => $items->approval_status ?? 'Unknown',
                    'Dinas' => $items->jns_dinas_tkt,
                    'NoSPPD' => $items->no_sppd === "-" ? $items->no_tkt : $items->no_sppd,
                    'PT' => $employee->company_name . ', ' . $employee->contribution_level_code,
                    'KodeBook' => $items->booking_code,
                    'HargaTiket' => $items->tkt_price,
                    'From' => $items->dari_tkt,
                    'To' => $items->ke_tkt,
                    'Name' => $items->np_tkt,
                    'NoTLP' => $items->tlp_tkt,
                    'TglBrkt1' => \Carbon\Carbon::parse($items->tgl_brkt_tkt)->format('d-F-Y') . ', ' . $items->jam_brkt_tkt,
                    'JenisTKT' => $items->type_tkt,
                    'Name2' => $items->type_tkt === 'Round Trip' ? $items->np_tkt : '',
                    'NoTLP2' => $items->type_tkt === 'Round Trip' ? $items->tlp_tkt : '',
                    'TglBrkt2' => $items->type_tkt === 'Round Trip' ? \Carbon\Carbon::parse($items->tgl_plg_tkt)->format('d-F-Y') . ', ' . $items->jam_plg_tkt : '',
                    'ket' => $items->ket_tkt,
                ];
            }
            $index++; // Tambahkan satu ke index setiap iterasi loop
        }

        return collect($combinedData);
    }


    public function headings(): array
    {
        return [
            // Tambahan judul kolom grup dengan kolom kosong di baris pertama
            ['No', 'User', 'Atasan', 'Status', 'Dinas/Cuti', 'No SPPD/Ticket', 'PT', 'Booking Code', 'Ticket Price', 'Destination', '', 'Departure Details', '', '', 'Ticket Type', 'Return Details', '', '', 'Note'],
            // Header kolom data
            ['', '', '', '', '', '', '', '', '', 'From', 'To', 'Name', 'Phone Number', 'Departure Date', '', 'Name', 'Phone Number', 'Departure Date', '']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF', // Warna putih
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => '228B22', // Warna kuning
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center horizontal
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,   // Center vertical
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Menggabungkan kolom pada baris pertama dan kedua untuk kolom tanpa grup
                $sheet->mergeCells('A1:A2'); // No
                $sheet->mergeCells('B1:B2'); // User
                $sheet->mergeCells('C1:C2'); // Atasan
                $sheet->mergeCells('D1:D2'); // Status
                $sheet->mergeCells('E1:E2'); // Dinas/Cuti
                $sheet->mergeCells('F1:F2'); // No SPPD/Ticket
                $sheet->mergeCells('G1:G2'); // PT
                $sheet->mergeCells('H1:H2'); // Booking Code
                $sheet->mergeCells('I1:I2'); // Ticket Price
                $sheet->mergeCells('O1:O2'); // Ticket Type
                $sheet->mergeCells('S1:S2'); // Note

                // Menggabungkan kolom dalam grup
                $sheet->mergeCells('J1:K1'); // Destination (From - To)
                $sheet->mergeCells('L1:N1'); // Detail Keberangkatan (Name, Phone Number, Departure Date)
                $sheet->mergeCells('P1:R1'); // Detail Kepulangan (Name, Phone Number, Departure Date)

                // Mengatur styling untuk dua baris heading
                $sheet->getStyle('A1:S2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // Warna teks putih
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '228B22'], // Warna latar belakang hijau tua
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Menambahkan border untuk seluruh data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Mengatur lebar kolom otomatis
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Merge cells for rows with the same no_tkt value
                $prevNoTkt = null;
                $startRow = 3; // Start from row 3 (after the header rows)
                for ($row = 3; $row <= $highestRow; $row++) {
                    $noTkt = $sheet->getCell('F' . $row)->getValue();
                    if ($noTkt === $prevNoTkt) {
                        $sheet->mergeCells('A' . $startRow . ':A' . $row);
                        $sheet->mergeCells('B' . $startRow . ':B' . $row);
                        $sheet->mergeCells('C' . $startRow . ':C' . $row);
                        $sheet->mergeCells('D' . $startRow . ':D' . $row);
                        $sheet->mergeCells('E' . $startRow . ':E' . $row);
                        $sheet->mergeCells('F' . $startRow . ':F' . $row);
                        $sheet->mergeCells('G' . $startRow . ':G' . $row);
                        $sheet->mergeCells('H' . $startRow . ':H' . $row);
                        $sheet->mergeCells('I' . $startRow . ':I' . $row);

                        $sheet->getStyle('A' . $startRow . ':I' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    } else {
                        $startRow = $row;
                    }
                    $prevNoTkt = $noTkt;
                }
            },
        ];
    }
}
