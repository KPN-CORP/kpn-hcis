<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\HealthCoverage;
use App\Models\HealthPlan;
use App\Models\MasterMedical;
use App\Models\Employee;
use App\Models\MasterPlafond;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Exceptions\ImportDataInvalidException;
use App\Exports\MedicalFailedImportExport;
use App\Mail\MedicalNotification;
use App\Mail\MedicalOverPlafondNotification;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportHealthCoverage implements ToModel
{
    private $batchRecords = [];
    private $failedRows = [];
    private $attachmentPath;
    private $today;
    private $base64Image;

    public function __construct($attachmentPath = null)
    {
        $this->attachmentPath = $attachmentPath
            ? json_encode([$attachmentPath])
            : null;
        $this->today = Carbon::today();
        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath);
        $this->base64Image = "data:image/png;base64," . base64_encode($imageContent);
    }

    public function generateNoMedic()
    {
        $currentYear = date("y");
        // Fetch the last no_medic number
        $lastCoverage = HealthCoverage::withTrashed() // Include soft-deleted records
            ->orderBy("no_medic", "desc")
            ->first();

        // Determine the next no_medic number
        if (
            $lastCoverage &&
            substr($lastCoverage->no_medic, 2, 2) == $currentYear
        ) {
            $lastNumber = (int) substr($lastCoverage->no_medic, 4); // Extract the last 6 digits
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format the next number as a 9-digit number starting with 'MD'
        $newNoMedic =
            "MD" . $currentYear . str_pad($nextNumber, 6, "0", STR_PAD_LEFT);

        return $newNoMedic;
    }

    public function model(array $row)
    {
        $employeeId = Employee::where("id", Auth::id())
            ->pluck("employee_id")
            ->first();

        if (
            $row[0] == "No" &&
            $row[1] == "Employee Name" &&
            $row[2] == "Employee ID"
        ) {
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
        if (
            $row[1] == $nameDummy ||
            $row[2] == $idDummy ||
            $row[4] == $invDummy ||
            $row[5] == $rsDummy ||
            $row[6] == $patDummy
        ) {
            throw new ImportDataInvalidException(
                "You can Import Dummy data to Database.",
            );
        }

        // Cek apakah Employee ID ada di database
        $employee = Employee::where("employee_id", $row[2])->first();
        if (!$employee) {
            $errorMessage = "Employee ID '{$row[2]}' tidak ditemukan di database.";
        } elseif ($employee->fullname !== $row[1]) {
            $errorMessage = "Employee Name '{$row[1]}' tidak sesuai dengan '{$employee->fullname}' yang berEmployee ID '{$row[2]}'.";
        }

        // Validasi apakah telah ada record yang sama
        $expectedRecord = HealthCoverage::where("employee_id", $row[2])
            ->where("no_invoice", $row[4])
            ->where("patient_name", $row[6])
            ->where("disease", $row[7])
            ->where("medical_type", $row[11])
            ->where("balance", $row[12])
            ->where("date", $row[8])
            ->first();
        if ($expectedRecord) {
            $errorMessage = "Transaksi Medical dengan Employee Name '{$row[1]}', Pasien '{$row[6]}', Invoice '{$row[4]}', Desease '{$row[7]}', Medical Type '{$row[11]}' dan Nominal '{$row[12]}' dan Tanggal '{$row[8]}' sudah pernah di ajukan.";
        }

        // Validasi Medical Type
        $expectedTypes = MasterMedical::pluck("name")->toArray();
        if (!in_array($row[11], $expectedTypes)) {
            $errorMessage =
                "Medical Type '{$row[11]}' tidak valid. Harus salah satu dari: " .
                implode(", ", $expectedTypes);
        }

        // Validasi format angka dan jumlah digit NIK
        if (!is_numeric($row[2])) {
            $errorMessage = "Employee ID harus berupa angka.";
        } elseif (strlen($row[2]) !== 11) {
            $errorMessage = "Jumlah digit NIK harus 11.";
        }

        if (!is_numeric($row[12])) {
            $errorMessage = "Amount harus berupa angka.";
        } elseif ($row[12] < 0) {
            $errorMessage = "Amount tidak boleh minus.";
        }

        if (!is_numeric($row[13])) {
            $errorMessage = "Amount BPJS Cover harus berupa angka.";
        } elseif ($row[13] < 0) {
            $errorMessage = "Amount BPJS Cover tidak boleh minus.";
        }

        $employeeCoveredAmount = null;
        $companyCoveredAmount = null;

        if (array_key_exists(14, $row) && !empty($row[14]) && is_numeric($row[14])) {
            $employeeCoveredAmount = $row[14];

            if ($employeeCoveredAmount < 0) {
                $errorMessage = "Employee Covered Amount tidak boleh minus.";
            }
        }

        if (array_key_exists(15, $row) && !empty($row[15]) && is_numeric($row[15])) {
            $companyCoveredAmount = $row[15];

            if ($companyCoveredAmount < 0) {
                $errorMessage = "Company Covered Amount tidak boleh minus.";
            }
        }

        // Validasi format tanggal
        if (is_numeric($row[8])) {
            $dateTime = Date::excelToDateTimeObject(intval($row[8]));
            $formattedDate = $dateTime->format("Y-m-d");
        } else {
            $date = \DateTime::createFromFormat("d/m/Y", $row[8]);
            if (!$date) {
                $errorMessage = "Format tanggal tidak valid.";
            } else {
                $formattedDate = $date->format("Y-m-d");
            }
        }

        // Validasi tanggal tidak melebihi hari ini
        if (isset($formattedDate) && $formattedDate > date("Y-m-d")) {
            $errorMessage = "Tanggal tidak boleh melebihi hari ini.";
        }

        // Jika ada error, simpan ke array gagal
        if ($errorMessage) {
            if (array_key_exists(15, $row)) {
                $row[16] = $errorMessage; // Simpan error di kolom ke-16
            } else {
                $row[14] = $errorMessage; // Simpan error di kolom ke-14
            }
            $this->failedRows[] = $row;
            return null; // Jangan simpan ke database
        }

        // Jika data valid, simpan ke database
        $healthCoverage = new HealthCoverage([
            "usage_id" => Str::uuid(),
            "employee_id" => $row[2],
            "contribution_level_code" => $employee->contribution_level_code,
            "no_medic" => $this->generateNoMedic(),
            "no_invoice" => $row[4],
            "hospital_name" => $row[5],
            "patient_name" => $row[6],
            "disease" => $row[7],
            "date" => $formattedDate,
            "coverage_detail" => $row[9],
            "period" => $row[10],
            "medical_type" => $row[11],
            "balance" => $row[12],
            "balance_uncoverage" => "0",
            "balance_verif" => $row[12],
            "balance_bpjs" => $row[13],
            "status" => "Done",
            "submission_type" => "F",
            "medical_proof" => $this->attachmentPath,
            "created_by" => $employeeId,
            "verif_by" => $employee->employee_id,
            "approved_by" => $employee->employee_id,
            "created_at" => now(),
            "approved_at" => now(),
            "employee_covered_amount" => $employeeCoveredAmount,
            "company_covered_amount" => $companyCoveredAmount
        ]);

        $this->batchRecords[] = $healthCoverage;

        return $healthCoverage;
    }

    public function afterImport()
    {
        // Group records by employee_id
        $groupedRecords = collect($this->batchRecords)->groupBy("employee_id");

        foreach ($this->batchRecords as $healthCoverage) {
            $this->performCalculations($healthCoverage); // Perhitungan hanya dilakukan di sini
        }

        // Kirim email setelah semua proses selesai
        foreach ($groupedRecords as $employeeId => $records) {
            $email = Employee::where("employee_id", $employeeId)
                ->pluck("email")
                ->first();
            if ($email) {
                $imagePath = public_path("images/kop.jpg");
                $imageContent = file_get_contents($imagePath);
                $base64Image =
                    "data:image/png;base64," . base64_encode($imageContent);

                try {
                    Mail::to($email)->send(
                        new MedicalNotification($records, $base64Image),
                    );
                } catch (\Exception $e) {
                    Log::error(
                        "Email Record Medical tidak terkirim: " .
                            $e->getMessage(),
                    );
                }
            }
        }

        $this->batchRecords = []; // Bersihkan batch
        return $this->failedRows;
    }

    private function performCalculations(HealthCoverage $healthCoverage)
    {
        $employee = Employee::where("employee_id", $healthCoverage->employee_id)->first();
        if (!$employee) {
            $this->calculateBalance($healthCoverage);
            Log::error("ImportHealthCoverage: Employee Not Found: " . $healthCoverage->employee_id);
            return;
        }

        $healthPlan = HealthPlan::where("employee_id", $employee->employee_id)
            ->where("medical_type", $healthCoverage->medical_type)
            ->where("period", $healthCoverage->period)
            ->first();
        if (!$healthPlan) {
            $this->calculateBalance($healthCoverage);
            Log::error("ImportHealthCoverage: Health Plan Not Found: " . $employee->employee_id . ", " . $healthCoverage->medical_type . ", " . $healthCoverage->period);
            return;
        }

        $plafond = MasterPlafond::where("group_name", $employee->job_level)
            ->where("medical_type", $healthPlan->medical_type)
            ->where("active", "T")
            ->first();
        if (!$plafond) {
            $this->calculateBalance($healthCoverage);
            Log::error("ImportHealthCoverage: Plafond Not Found: " . $employee->job_level . ", " . $healthPlan->medical_type);
            return;
        }

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

        if ($healthPlan->balance < 0 && $healthPlan->over_plafond_email_sent_date == null && (strtolower($employee->group_company) == "downstream")) {
            Mail::to($employee->email)->bcc('dali.kewara@kpn-corp.com')->queue(
                (new MedicalOverPlafondNotification(
                    $plafond,
                    $healthPlan,
                    $employee,
                    $this->base64Image
                ))->onQueue('hcis')
            );

            $healthPlan->over_plafond_email_sent_date = $this->today;
        }

        $healthPlan->save();
        $this->calculateBalance($healthCoverage);
    }

    private function calculateBalance(HealthCoverage $healthCoverage)
    {
        $healthCoverage->save();
    }
}
