<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;

class ApiEmployeeController extends Controller
{
    // Ambil semua data karyawan
    public function index()
    {
        $employees = Employee::all();
        return EmployeeResource::collection($employees);
    }

    // Ambil data karyawan berdasarkan ID atau kolom lainnya
    public function show(Request $request, $id)
    {
        // Mencari berdasarkan ID
        $employee = Employee::with('healthPlans')->where('employee_id', $id)->first();

        // Jika tidak ditemukan, balikan respon 404
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return new EmployeeResource($employee);
    }

    // Mencari data karyawan berdasarkan parameter tertentu (opsional)
    public function search(Request $request)
    {
        $query = Employee::query();

        // Filter berdasarkan parameter
        if ($request->has('fullname')) {
            $query->where('fullname', 'like', '%' . $request->fullname . '%');
        }

        if ($request->has('email')) {
            $query->where('email', $request->email);
        }

        if ($request->has('designation')) {
            $query->where('designation', $request->designation);
        }

        $employees = $query->get();

        // Jika tidak ada hasil
        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No employees found'], 404);
        }

        return EmployeeResource::collection($employees);
    }
}
