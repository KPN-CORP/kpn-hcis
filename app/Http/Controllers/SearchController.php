<?php

namespace App\Http\Controllers;

use App\Models\Dependents;
use App\Models\Employee;
use App\Models\HomeTrip;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function searchNik(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $limit = 10; // Adjust as needed

        $employees = Employee::select('ktp', 'fullname')
            ->where(function ($query) use ($searchTerm) {
                $query->where('fullname', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('ktp', 'LIKE', '%' . $searchTerm . '%');
            })
            // ->where('employee_id', '!=', $request->input('employeeId')) // Exclude employee_id matching user_id
            ->limit($limit) // Limit the number of results returned
            ->get();

        return response()->json($employees);
    }
    public function searchPassenger(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $employee_id = Auth::user()->employee_id;
        $currentYear = date('Y');

        // Fetch the authenticated employee's data and match the search term
        $employee = HomeTrip::select('name as fullname', DB::raw("'Me' as relation_type"))
            ->where('employee_id', $employee_id)
            ->where('period', $currentYear)
            ->where('relation_type', '=', 'employee')
            ->where('quota', '>', 0) // Only include employee if they have a valid quota
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->first();

        // Fetch dependents that match the search term and have a valid quota
        $dependents = Dependents::select('name as fullname', 'relation_type')
            ->where('employee_id', $employee_id)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('relation_type', 'LIKE', '%' . $searchTerm . '%');
            })
            ->get();

        // Return both datasets in a structured JSON response
        return response()->json([
            'employee' => $employee ? [$employee] : [], // Return employee if found
            'dependents' => $dependents,
        ]);
    }
}
