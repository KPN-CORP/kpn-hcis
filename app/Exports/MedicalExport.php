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

class MedicalExport implements FromCollection, WithHeadings, WithStyles, WithEvents
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
        $userRole = auth()->user()->roles->first();
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

        // Query HealthCoverage berdasarkan Employee ID
        $employeeIds = $employees->pluck('employee_id');
        $healthCoverageQuery = HealthCoverage::whereIn('employee_id', $employeeIds)->where('status', '!=', 'Draft');
        if (!empty($this->startDate) && !empty($this->endDate)) {
            
            $startDate = $this->startDate . ' 00:00:00';
            $endDate = $this->endDate . ' 23:59:59';
        
            $healthCoverageQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        if (!empty($this->statusMDC)) {
            $healthCoverageQuery->where('status', $this->statusMDC);
        }
        $healthCoverages = $healthCoverageQuery->orderBy('created_at', 'desc')->get();

        $medical_plans = HealthPlan::where('period', $currentYear)->get();
        $balances = [];
        foreach ($medical_plans as $plan) {
            $balances[$plan->employee_id][$plan->medical_type] = $plan->balance;
        }

        // Gabungkan Data
        $combinedData = [];
        foreach ($employees as $employee) {
            $employeeCoverages = $healthCoverages->where('employee_id', $employee->employee_id);

            foreach ($employeeCoverages as $coverage) {
                // if ($coverage->status == 'Done') {
                    $combinedData[] = [
                        'number' => count($combinedData) + 1,
                        'NoMed' => $coverage->no_medic,
                        'Employee ID' => $employee->employee_id,
                        'Name' => $employee->fullname,
                        'Transaction Date' => \Carbon\Carbon::parse($coverage->date)->format('d-F-Y'),
                        'Submission Date' => \Carbon\Carbon::parse($coverage->created_at)->format('d-F-Y'),
                        'Patient Name' => $coverage->patient_name,
                        'Hospital Name' => $coverage->hospital_name,
                        'Group' => $employee->designation_name,
                        'Disease' => $coverage->disease,
                        'Status' => $coverage->status,
                        'Reimburse' => $coverage->submission_type = ($coverage->submission_type == 'T') ? 'Reimburse' : 'Non Reimburse',
                        'NoRek' => $employee->bank_name . ' - ' . $employee->bank_account_number,
                        'NoInvoice' => $coverage->no_invoice,
                        'MedicalType' => $coverage->medical_type,
                        'Amount' => 'Rp ' . number_format($coverage->balance, 0, ',', '.'),
                        'AmountUncoverage' => 'Rp ' . number_format($coverage->balance_uncoverage, 0, ',', '.'),
                        'AmountVerify' => 'Rp ' . number_format($coverage->balance_verif, 0, ',', '.'),
                        'AdminNotes' => $coverage->admin_notes,
                        'PT' => $employee->company_name,
                        'CostCenter' => $employee->contribution_level_code,
                        'JobLevel' => $employee->job_level,
                        'GroupCompany' => $employee->group_company,
                    ];
                // }
            }
        }

        return collect($combinedData);
    }


    public function headings(): array
    {
        // Base headings
        $headings = [
            'No',
            'No Medical',
            'Employee ID',
            'Employee Name',
            'Transaction Date',
            'Submission Date',
            'Patient Name',
            'Hospital Name',
            'Designation',
            'Desease',
            'Status',
            'Type',
            'Account Detail',
            'No Invoice',
            'Medical Type',
            'Amount',
            'Amount Uncoverage',
            'Amount Verify',
            'Admin Notes',
            'PT',
            'Cost Center',
            'Job Level',
            'Group Company',
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
