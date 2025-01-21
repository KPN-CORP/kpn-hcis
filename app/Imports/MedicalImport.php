<?php

namespace App\Imports;

use App\Models\HealthPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MedicalImport implements ToModel
{
    protected $importedData = [];

    public function model(array $row)
    {
        // Validasi jika ID atau employee_id tidak valid (harus integer)
        if (!is_numeric($row[1])) {
            // Jika tidak valid, skip row ini
            return null;
        }

        $userId = Auth::id(); // Mendapatkan ID user yang sedang login

        $transaction = new HealthPlan([
            'usage_id' => Str::uuid(),
            'employee_id' => $row[1],
            'no_medic' => $row[2],
            'no_invoice' => $row[3],
            'hospital_name' => $row[4],
            'patient_name' => $row[5],
            'disease' => $row[6],
            'date' => $row[7],
            'coverage_detail' => $row[8],
            'period' => $row[9],
            'medical_type' => $row[10],
            'balance' => $row[11],
            'balance_uncoverage' => $row[12],
            'balance_verif' => $row[13],
            'status' => $row[14],
            'created_by' => $userId,
        ]);

        // Mengumpulkan data yang diimpor
        $this->importedData[] = $transaction;

        return $transaction;
    }

    public function finalize()
    {
        // Debug: Menampilkan data yang telah diimpor
        dd($this->importedData);
    }
}
