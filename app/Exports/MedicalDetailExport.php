<?php

namespace App\Exports;

use App\Models\Dependents;
use App\Models\Employee;
use App\Models\MasterDisease;
use App\Models\HealthPlan;
use App\Models\HealthCoverage;
use App\Models\MasterMedical;
use App\Models\Company;
use App\Models\Location;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class MedicalDetailExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = $employee_id;
    }

    public function collection()
    {
        $medicalGroup = HealthCoverage::select(
            'no_medic',
            'date',
            'period',
            'hospital_name',
            'patient_name',
            'disease',
            DB::raw('SUM(CASE WHEN medical_type = "Child Birth" THEN balance ELSE 0 END) as child_birth_total'),
            DB::raw('SUM(CASE WHEN medical_type = "Inpatient" THEN balance ELSE 0 END) as inpatient_total'),
            DB::raw('SUM(CASE WHEN medical_type = "Outpatient" THEN balance ELSE 0 END) as outpatient_total'),
            DB::raw('SUM(CASE WHEN medical_type = "Glasses" THEN balance ELSE 0 END) as glasses_total'),
            'status',
            DB::raw('MAX(created_at) as latest_created_at')

        )
            ->where('employee_id', $this->employee_id)
            ->where('status', '!=', 'Draft')
            ->groupBy('no_medic', 'date', 'period', 'hospital_name', 'patient_name', 'disease', 'status')
            ->orderBy('latest_created_at', 'desc')
            ->get();

        $counter = 1;
        $medicalGroupWithNumbers = $medicalGroup->map(function ($item) use (&$counter) {
            return [
                'number' => $counter++, // Nomor urut di bagian depan
                'date' => \Carbon\Carbon::parse($item->date)->format('d-F-Y'),
                'period' => $item->period,
                'no_medic' => $item->no_medic,
                'hospital_name' => $item->hospital_name,
                'patient_name' => $item->patient_name,
                'disease' => $item->disease,
                'status' => $item->status,
                'child_birth_total' => $item->child_birth_total,
                'inpatient_total' => $item->inpatient_total,
                'outpatient_total' => $item->outpatient_total,
                'glasses_total' => $item->glasses_total,
            ];
        });

        return $medicalGroupWithNumbers;
    }

    public function headings(): array
    {
        // Base headings
        $headings = [
            'No',
            'Date',
            'Period',
            'No Medic',
            'Hospital Name',
            'Patient Name',
            'Disease',
            'Status',
        ];

        // Fetch dynamic medical types and append to headings
        $master_medical_heading = MasterMedical::all();
        foreach ($master_medical_heading as $medical_item) {
            $headings[] = $medical_item->name; // Append each medical type to headings
        }

        return $headings;
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
            },
        ];
    }
}
