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

class MedicalExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $stat;
    protected $customSearch;

    public function __construct($stat, $customSearch)
    {
        $this->stat = $stat;
        $this->customSearch = $customSearch;
    }

    public function collection()
    {
        $currentYear = date('Y');

        $master_medical = MasterMedical::all();
        $query = Employee::with(['employee', 'statusReqEmployee', 'statusSettEmployee']);

        if (!empty($this->stat)) {
            $query->where('office_area', $this->stat);
        }
        if (!empty($this->customSearch)) {
            $query->where('fullname', 'like', '%' . $this->customSearch . '%');
        }

        $med_employee = $query->orderBy('created_at', 'desc')->get();

        $medical_plans = HealthPlan::where('period', $currentYear)->get();

        $balances = [];
        foreach ($medical_plans as $plan) {
            $balances[$plan->employee_id][$plan->medical_type] = $plan->balance;
        }

        foreach ($med_employee as $transaction) {
            $transaction->ReqName = $transaction->statusReqEmployee ? $transaction->statusReqEmployee->fullname : '';
            $transaction->settName = $transaction->statusSettEmployee ? $transaction->statusSettEmployee->fullname : '';

            $employeeMedicalPlan = $medical_plans->where('employee_id', $transaction->employee_id)->first();
            $transaction->period = $employeeMedicalPlan ? $employeeMedicalPlan->period : '-';
        }

        return $med_employee->map(function ($transaction, $index) use ($balances, $master_medical) {
            $data = [
                'No'               => $index + 1,
                'NIK'              => $transaction->kk,
                'Employee ID'      => $transaction->employee_id,
                'Employee Name'    => $transaction->fullname,
                'Join Date'        => \Carbon\Carbon::parse($transaction->date_of_joining)->format('d-F-Y'),
                'Period'           => $transaction->period,
                'Created At'       => $transaction->created_at,
            ];

            foreach ($master_medical as $medical_item) {
                $data[$medical_item->name] = isset($balances[$transaction->employee_id][$medical_item->name]) ? $balances[$transaction->employee_id][$medical_item->name] : 0;
            }

            return $data;
        });
    }

    public function headings(): array
    {
        // Base headings
        $headings = [
            'No',
            'NIK',
            'Employee ID',
            'Name',
            'Join Date',
            'Period',
        ];

        // Fetch dynamic medical types and append to headings
        $master_medical_heading = MasterMedical::all();
        foreach ($master_medical_heading as $medical_item) {
            $headings[] = $medical_item->name; // Append each medical type to headings
        }

        // Return the final headings array
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
