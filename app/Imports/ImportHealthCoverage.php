<?php

namespace App\Imports;

use App\Models\HealthCoverage;
use App\Models\HealthPlan;
use App\Models\MasterMedical;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Exceptions\ImportDataInvalidException;
use App\Exports\MedicalFailedImportExport;
use App\Mail\MedicalNotification;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;


class ImportHealthCoverage implements ToModel
{
    private $batchRecords = [];
    private $failedRows = [];
    private $attachmentPath;

    public function __construct($attachmentPath = null)
    {
        $this->attachmentPath = $attachmentPath ? json_encode([$attachmentPath]) : null;
    }

    public function generateNoMedic()
    {
        $currentYear = date('y');
        // Fetch the last no_medic number
        $lastCoverage = HealthCoverage::withTrashed() // Include soft-deleted records
            ->orderBy('no_medic', 'desc')
            ->first();

        // Determine the next no_medic number
        if ($lastCoverage && substr($lastCoverage->no_medic, 2, 2) == $currentYear) {
            $lastNumber = (int) substr($lastCoverage->no_medic, 4); // Extract the last 6 digits
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format the next number as a 9-digit number starting with 'MD'
        $newNoMedic = 'MD' . $currentYear . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return $newNoMedic;
    }

    public function model(array $row)
    {
        $employeeId = Employee::where('id', Auth::id())->pluck('employee_id')->first();

        if ($row[0] == 'No' && $row[1] == 'Employee Name' && $row[2] == 'Employee ID') {
            return null;
        }

        if (empty(array_filter($row))) {
            return null;
        }

        $errorMessage = null;

        $nameDummy = "Write Name Employee Here";
        $idDummy = "01111111111";
        $invDummy = "123/TestVioce/2000";
        $rsDummy = "RS. Hospital Dummy";
        $patDummy = "John Doe";
        if ($row[1] == $nameDummy || $row[2] == $idDummy || $row[4] == $invDummy || $row[5] == $rsDummy || $row[6] == $patDummy) {
            throw new ImportDataInvalidException("You can Import Dummy data to Database.");
        }

        // Cek apakah Employee ID ada di database
        $employee = Employee::where('employee_id', $row[2])->first();
        if (!$employee) {
            $errorMessage = "Employee ID '{$row[2]}' tidak ditemukan di database.";
        } elseif ($employee->fullname !== $row[1]) {
            $errorMessage = "Employee Name '{$row[1]}' tidak sesuai dengan '{$employee->fullname}' yang berEmployee ID '{$row[2]}'.";
        }

        // Validasi apakah telah ada record yang sama
        $expectedRecord = HealthCoverage::where('employee_id', $row[2])
        ->where('no_invoice', $row[4])
        ->where('patient_name', $row[6])
        ->where('disease', $row[7])
        ->where('medical_type', $row[11])
        ->where('balance', $row[12])
        ->where('date', $row[8])
        ->first();
        if ($expectedRecord) {
            $errorMessage = "Transaksi Medical dengan Employee Name '{$row[1]}', Pasien '{$row[6]}', Invoice '{$row[4]}', Desease '{$row[7]}', Medical Type '{$row[11]}' dan Nominal '{$row[12]}' dan Tanggal '{$row[8]}' sudah pernah di ajukan.";
        }

        // Validasi Medical Type
        $expectedTypes = MasterMedical::pluck('name')->toArray();
        if (!in_array($row[11], $expectedTypes)) {
            $errorMessage = "Medical Type '{$row[11]}' tidak valid. Harus salah satu dari: " . implode(", ", $expectedTypes);
        }

        // Validasi format angka
        if (!is_numeric($row[2])) {
            $errorMessage = "Employee ID harus berupa angka.";
        }
        if (!is_numeric($row[12])) {
            $errorMessage = "Amount harus berupa angka.";
        }

        // Validasi format tanggal
        if (is_numeric($row[8])) {
            $dateTime = Date::excelToDateTimeObject(intval($row[8]));
            $formattedDate = $dateTime->format('Y-m-d');
        } else {
            $date = \DateTime::createFromFormat('d/m/Y', $row[8]);
            if (!$date) {
                $errorMessage = "Format tanggal tidak valid.";
            } else {
                $formattedDate = $date->format('Y-m-d');
            }
        }

        // Jika ada error, simpan ke array gagal
        if ($errorMessage) {
            $row[14] = $errorMessage; // Simpan error di kolom ke-14
            $this->failedRows[] = $row;
            return null; // Jangan simpan ke database
        }

        // Jika data valid, simpan ke database
        $healthCoverage = new HealthCoverage([
            'usage_id' => Str::uuid(),
            'employee_id' => $row[2],
            'contribution_level_code' => $employee->contribution_level_code,
            'no_medic' => $this->generateNoMedic(),
            'no_invoice' => $row[4],
            'hospital_name' => $row[5],
            'patient_name' => $row[6],
            'disease' => $row[7],
            'date' => $formattedDate,
            'coverage_detail' => $row[9],
            'period' => $row[10],
            'medical_type' => $row[11],
            'balance' => $row[12],
            'balance_uncoverage' => '0',
            'balance_verif' => $row[12],
            'balance_bpjs' => $row[13],
            'status' => 'Done',
            'submission_type' => 'F',
            'medical_proof' => $this->attachmentPath,
            'created_by' => $employeeId,
            'verif_by' => $employee->employee_id,
            'approved_by' => $employee->employee_id,
            'created_at' => now(),
            'approved_at' => now(),
        ]);

        $this->batchRecords[] = $healthCoverage;

        return $healthCoverage;
    }

    public function afterImport()
    {
        // Group records by employee_id
        $groupedRecords = collect($this->batchRecords)->groupBy('employee_id');

        foreach ($this->batchRecords as $healthCoverage) {
            $this->performCalculations($healthCoverage); // Perhitungan hanya dilakukan di sini
        }

        // Kirim email setelah semua proses selesai
        foreach ($groupedRecords as $employeeId => $records) {
            $email = Employee::where('employee_id', $employeeId)->pluck('email')->first();
            if ($email) {
                $imagePath = public_path('images/kop.jpg');
                $imageContent = file_get_contents($imagePath);
                $base64Image = "data:image/png;base64," . base64_encode($imageContent);

                try {
                    Mail::to($email)->send(new MedicalNotification($records, $base64Image));
                } catch (\Exception $e) {
                    Log::error('Email Record Medical tidak terkirim: ' . $e->getMessage());
                }
            }
        }

        $this->batchRecords = []; // Bersihkan batch
        return $this->failedRows;
    }

    private function performCalculations(HealthCoverage $healthCoverage)
    {
        $healthPlan = HealthPlan::where('employee_id', $healthCoverage->employee_id)
            ->where('medical_type', $healthCoverage->medical_type)
            ->where('period', $healthCoverage->period)
            ->first();

        if ($healthPlan) {
            $initialBalance = $healthPlan->balance;

            // if ($initialBalance > 0) {
                $healthPlan->balance -= $healthCoverage->balance;
            // }

            if ($initialBalance >= 0 && $healthCoverage->balance > $initialBalance) {
                $healthCoverage->balance_uncoverage = $healthCoverage->balance - $initialBalance;
            } elseif ($initialBalance < 0) {
                $healthCoverage->balance_uncoverage = $healthCoverage->balance;
            } else {
                $healthCoverage->balance_uncoverage = 0;
            }
            // dd($healthPlan->balance);

            $healthPlan->save();
        }

        $this->calculateBalance($healthCoverage);
    }

    private function calculateBalance(HealthCoverage $healthCoverage)
    {
        $healthCoverage->save();
    }
}
