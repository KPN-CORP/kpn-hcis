<?php

namespace App\Exports;

use App\Models\Dependents;
use App\Models\Employee;
use App\Models\MasterDisease;
use App\Models\HealthPlan;
use App\Models\HealthCoverage;
use App\Models\MasterMedical;
use App\Models\MasterBusinessUnit;
use App\Models\Company;
use App\Models\Location;
use FontLib\Table\Type\fpgm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class MedicalPlafondExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $statusMDC;
    protected $stat;
    protected $customSearch;
    protected $startDate;
    protected $endDate;
    protected $unit;

    public function __construct($statusMDC, $stat, $customSearch, $startDate, $endDate, $unit)
    {
        $this->statusMDC = $statusMDC;
        $this->stat = $stat;
        $this->customSearch = $customSearch;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->unit = $unit;
    }

    public function collection()
    {

        $currentYear = date('Y');
        $userRole = auth()->user()->roles->last();
        $roleRestriction = json_decode($userRole->restriction, true);

        $restrictedWorkAreas = $roleRestriction['work_area_code'] ?? [];
        $restrictedGroupCompanies = $roleRestriction['group_company'] ?? [];

        // Query Employee dengan filter work_area_code jika ada restriction
        $employeeQuery = Employee::with(['employee', 'statusReqEmployee', 'statusSettEmployee']);

        if (!empty($restrictedWorkAreas)) {
            $employeeQuery->whereIn('work_area_code', $restrictedWorkAreas);
        }
        if (!empty($restrictedGroupCompanies)) {
            $employeeQuery->whereIn('group_company', $restrictedGroupCompanies);
        }

        // Filter tambahan
        if (!empty($this->stat)) {
            $employeeQuery->where('group_company', $this->stat);
        }
        if (!empty($this->customSearch)) {
            $employeeQuery->where('fullname', 'like', '%' . $this->customSearch . '%');
        }
        if (!empty($this->unit)) {
            $employeeQuery->where('work_area_code', $this->unit);
        }

        $employees = $employeeQuery->orderBy('created_at', 'desc')->get();

        $employeeIds = $employees->pluck('employee_id');
        $medical_plans = DB::table('mdc_plans')
            ->select(
                'employee_id',
                'period',
                DB::raw("MAX(CASE WHEN medical_type = 'Outpatient' THEN balance END) AS Outpatient"),
                DB::raw("MAX(CASE WHEN medical_type = 'Inpatient' THEN balance END) AS Inpatient"),
                DB::raw("MAX(CASE WHEN medical_type = 'Maternity' THEN balance END) AS Maternity"),
                DB::raw("MAX(CASE WHEN medical_type = 'Glasses' THEN balance END) AS Glasses")
            )
            ->groupBy('employee_id', 'period');

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $startYear = Carbon::parse($this->startDate)->year;
            $endYear   = Carbon::parse($this->endDate)->year;
            $years = range($startYear, $endYear);

            $medical_plans->whereIn('period', $years);
        }
        $medical_plans->whereIn('employee_id', $employeeIds);

        $results = $medical_plans->get();

        $combinedData = [];
        foreach ($employees as $employee) {
            $employeePlafond = $results->where('employee_id', $employee->employee_id);

            foreach ($employeePlafond as $plafond) {                   
                $combinedData[] = [
                    'number' => count($combinedData) + 1,
                    'Employee ID' => $employee->employee_id,
                    'Name' => $employee->fullname,
                    'Period' => $plafond->period,
                    'Outpatient' => $plafond->Outpatient,
                    'Inpatient' => $plafond->Inpatient,
                    'Maternity' => $plafond->Maternity,
                    'Glasses' => $plafond->Glasses,
                ];
            }
        }
        return collect($combinedData);
    }


    public function headings(): array
    {
        // Base headings
        $headings = [
            'No',
            'Employee ID',
            'Employee Name',
            'Period',
            'Outpatient',
            'Inpatient',
            'Maternity',
            'Glasses',
        ];

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
