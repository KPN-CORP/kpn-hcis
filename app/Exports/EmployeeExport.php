<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class EmployeeExport implements FromView, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    protected $groupCompany;
    protected $location;
    protected $company;

    public function __construct($groupCompany, $location, $company)
    {
        $this->groupCompany = $groupCompany;
        $this->location = $location;
        $this->company = $company;
    }

    public function view(): View
    {
        $query = Employee::query()->whereNull('deleted_at');

        // Apply filters if they are provided
        if ($this->groupCompany) {
            $query->where('group_company', $this->groupCompany);
        }

        if ($this->location) {
            $query->where('work_area_code', $this->location);
        }

        if ($this->company) {
            $query->where('contribution_level_code', $this->company);
        }

        $data = $query->get();
        foreach ($data as $employee) {
            $employee->access_menu = json_decode($employee->access_menu, true);
        }

        return view('exports.employee', compact('data'));
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFF00']]
            ],
        ];
    }

}
