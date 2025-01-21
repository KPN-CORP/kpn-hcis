<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeDetailExport;
use App\Exports\EmployeeExport;
use App\Exports\GoalExport;
use App\Exports\InitiatedExport;
use App\Exports\NotInitiatedExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ExportExcelController extends Controller
{
    public function export(Request $request) 
    {
        $reportType = $request->export_report_type;
        $groupCompany = $request->export_group_company;
        $company = $request->export_company;
        $location = $request->export_location;
        $admin = 0;

        if($reportType==='Goal'){
            $goal = new GoalExport($groupCompany, $location, $company, $admin);
            return Excel::download($goal, 'goals.xlsx');
        }
        if($reportType==='Employee'){
            $employee = new EmployeeExport($groupCompany, $location, $company);
            return Excel::download($employee, 'employee.xlsx');
        }
        return;

    }

    public function exportAdmin(Request $request) 
    {
        $reportType = $request->export_report_type;
        $groupCompany = $request->export_group_company;
        $company = $request->export_company;
        $location = $request->export_location;
        $admin = 1;

        if($reportType==='Goal'){
            $goal = new GoalExport($groupCompany, $location, $company, $admin);
            return Excel::download($goal, 'goals.xlsx');
        }
        if($reportType==='Employee'){
            $employee = new EmployeeExport($groupCompany, $location, $company);
            return Excel::download($employee, 'employee.xlsx');
        }
        return;

    }

    public function notInitiated(Request $request) 
    {
        $employee_id = $request->employee_id;

        $data = new NotInitiatedExport($employee_id);
        return Excel::download($data, 'employee_not_initiated_goals.xlsx');

    }

    public function initiated(Request $request) 
    {
        $employee_id = $request->employee_id;

        $data = new InitiatedExport($employee_id);
        return Excel::download($data, 'employee_initiated_goals.xlsx');

    }

    public function exportreportemp() 
    {
        return Excel::download(new EmployeeDetailExport, 'employees_detail.xlsx');
    }
}
