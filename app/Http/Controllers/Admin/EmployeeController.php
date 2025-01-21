<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Location;

class EmployeeController extends Controller
{
    function employee() {
        $link = 'employee';

        $employees = employee::all();
        $locations = Location::orderBy('area')->get();

        return view('pages.employees.employee', [
            'link' => $link,
            'employees' => $employees,
            'locations' => $locations,
        ]);        
    }
}
