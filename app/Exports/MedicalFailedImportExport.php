<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MedicalFailedImportExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $failedRows;

    public function __construct(array $failedRows)
    {
        $this->failedRows = $failedRows;
    }

    public function array(): array
    {
        return $this->failedRows;
    }

    public function headings(): array
    {
        return [
            'No', 
            'Employee Name', 
            'Employee ID', 
            'Contribution Level Code',
            'No Invoice', 
            'Hospital Name', 
            'Patient Name', 
            'Disease',
            'Date', 
            'Coverage Detail', 
            'Period', 
            'Medical Type',
            'Amount', 
            'Amount BPJS', 
            'Error Message' // Kolom tambahan untuk error
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
                $highestRow = $sheet->getHighestRow(); // Get highest row number  
                $highestColumn = $sheet->getHighestColumn(); // Get highest column letter  

                // Apply border to the entire data range  
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([  
                    'borders' => [  
                        'allBorders' => [  
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,  
                        ],  
                    ],  
                ]);  

                // Adjust column widths automatically  
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // Get highest column index  
                for ($col = 1; $col <= $highestColumnIndex; $col++) {  
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col); // Convert to letter  
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);  
                }  

                $sheet->getStyle('B1:B' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);  

                // Menambahkan catatan di bawah data  
                $note = "Silahkan Upload file ini saja setelah revisi, karena data sebelumnya telah berhasil, dan hapus note ini.";  
                $noteRow = $highestRow + 2; // Menambahkan catatan dua baris di bawah data  
                $sheet->setCellValue('A' . $noteRow, $note);  
                $sheet->mergeCells('A' . $noteRow . ':' . $highestColumn . $noteRow); // Menggabungkan sel untuk catatan  
                $sheet->getStyle('A' . $noteRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // Memusatkan teks catatan  
            },  
        ];  
    }
}
