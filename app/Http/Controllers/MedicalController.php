<?php

namespace App\Http\Controllers;

use App\Models\Dependents;
use App\Models\Employee;
use App\Models\MasterDisease;
use App\Models\HealthPlan;
use App\Models\HealthCoverage;
use App\Models\MasterMedical;
use App\Models\Company;
use App\Models\Location;
use App\Models\MasterPlafond;
use App\Models\MasterBusinessUnit;
use App\Models\CATransaction;
use App\Models\BusinessTrip;
use App\Models\Hotel;
use App\Models\Tiket;
use App\Models\MedicalType;
use App\Models\MedicalHospital;
use App\Mail\MedicalOverPlafondNotification;
// use App\Models\ELogFirstReceipt;
// use App\Helpers\ELog as ELogHelper;
use App\Services\ELogService;
use App\Jobs\ELogInsertFirstReceiptJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Imports\ImportHealthCoverage;
use App\Exports\MedicalExport;
use App\Exports\MedicalPlafondExport;
use App\Exports\MedicalDetailExport;
use App\Mail\MedicalNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Jobs\MedicalPaidDateSyncJob;

class MedicalController extends Controller
{
    protected $permissionLocations;
    protected $permissionCompanies;
    protected $permissionGroupCompanies;
    protected $roles;

    public function __construct()
    {
        // $this->category = 'Goals';
        $this->roles = Auth()->user()->roles;

        $restrictionData = [];
        if (!is_null($this->roles) && $this->roles->isNotEmpty()) {
            $restrictionData = json_decode(
                $this->roles->last()->restriction,
                true,
            );
        }

        $this->permissionGroupCompanies =
            $restrictionData["group_company"] ?? [];
        $this->permissionCompanies =
            $restrictionData["contribution_level_code"] ?? [];
        $this->permissionLocations = $restrictionData["work_area_code"] ?? [];
    }

    public function medical()
    {
        $employee_id = Auth::user()->employee_id;
        $family = Dependents::orderBy("date_of_birth", "asc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical = HealthCoverage::orderBy("created_at", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical_plan = HealthPlan::orderBy("period", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medicalGroup = HealthCoverage::select(
            "no_medic",
            "date",
            "period",
            "hospital_name",
            "patient_name",
            "disease",
            "verif_by",
            "balance_verif",
            "approved_by",
            "doc_status",
            DB::raw("DATE_FORMAT(paid_date, '%Y-%m-%d') as paid_date"),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Maternity" THEN balance ELSE 0 END) as maternity_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Inpatient" THEN balance ELSE 0 END) as inpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Outpatient" THEN balance ELSE 0 END) as outpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Glasses" THEN balance ELSE 0 END) as glasses_total',
            ),
            "status",
            DB::raw("MAX(created_at) as latest_created_at"),
            "is_revise"
        )
            ->where("employee_id", $employee_id)
            ->groupBy(
                "no_medic",
                "date",
                "period",
                "hospital_name",
                "patient_name",
                "disease",
                "status",
                "verif_by",
                "balance_verif",
                "approved_by",
                "doc_status",
                "is_revise",
                "paid_date"
            )
            ->orderBy("latest_created_at", "desc")
            ->get();

        $medicalGroup->map(function ($item) {
            $approvedEmployee = Employee::where(
                "employee_id",
                $item->approved_by,
            )->first();

            $item->approved_by_fullname = $approvedEmployee
                ? $approvedEmployee->fullname
                : null;
            $item->total_per_no_medic =
                $item->maternity_total +
                $item->inpatient_total +
                $item->outpatient_total +
                $item->glasses_total;
            return $item;
        });

        $bissnisUnit = Employee::where("employee_id", $employee_id)->pluck(
            "group_company",
        );
        $gaApproval = MasterBusinessUnit::where(
            "nama_bisnis",
            $bissnisUnit,
        )->first();
        $gaFullname = Employee::where(
            "employee_id",
            $gaApproval->approval_medical,
        )->pluck("fullname");

        $rejectMedic = HealthCoverage::where("employee_id", $employee_id)
            ->where("status", "Rejected")
            ->get()
            ->keyBy("no_medic");

        $employeeIds = $rejectMedic
            ->pluck("employee_id")
            ->merge($rejectMedic->pluck("rejected_by"))
            ->unique();

        $employees = Employee::whereIn("employee_id", $employeeIds)->pluck(
            "fullname",
            "employee_id",
        );

        $rejectMedic->transform(function ($item) use ($employees) {
            $item->employee_fullname = $employees->get($item->employee_id);
            $item->rejected_by_fullname = $employees->get($item->rejected_by);
            return $item;
        });

        $medical = $medicalGroup->map(function ($item) use ($employee_id) {
            $usageId = HealthCoverage::where("no_medic", $item->no_medic)
                ->where("employee_id", $employee_id)
                ->value("usage_id");

            $item->usage_id = $usageId;

            MedicalPaidDateSyncJob::dispatch($usageId);

            return $item;
        });

        $master_medical = MasterMedical::where("active", "T")->get();

        $formatted_data = [];
        foreach ($medical_plan as $plan) {
            $formatted_data[$plan->period][$plan->medical_type] =
                $plan->balance;
        }

        $employees_cast = Employee::where("employee_id", $employee_id)->get();
        $currentYear = date("Y");

        foreach ($employees_cast as $employee) {
            $startDate = $employee->date_of_joining;
            $job_level = $employee->job_level;
            $endDate = date("Y-12-31");

            $startDate = date_create($startDate);
            $endDate = date_create($endDate);
            $difference = date_diff($startDate, $endDate);
            $yearsWorked = $difference->y;

            $plafond_list = MasterPlafond::where(
                "group_name",
                $job_level,
            )->get();

            if ($yearsWorked > 0) {
                foreach ($plafond_list as $plafond_lists) {
                    $existingHealthPlan = HealthPlan::where(
                        "employee_id",
                        $employee->employee_id,
                    )
                        ->where("period", $currentYear)
                        ->where("medical_type", $plafond_lists->medical_type)
                        ->first();

                    if (!$existingHealthPlan) {
                        $newHealthPlan = HealthPlan::create([
                            "plan_id" => (string) Str::uuid(),
                            "employee_id" => $employee->employee_id,
                            "medical_type" => $plafond_lists->medical_type,
                            "balance" => $plafond_lists->balance,
                            "period" => $currentYear,
                            "created_by" => $employee_id,
                            "created_at" => now(),
                        ]);

                        if ($newHealthPlan) {
                            session()->flash("refresh", true);
                        }
                    }
                }
            } elseif ($yearsWorked == 0) {
                $startDate = date_create($employee->date_of_joining);

                $tanggal = date_format($startDate, "d");
                if ($tanggal > 15) {
                    $bulan_awal = date_format($startDate, "m") + 1; // Bulan setelahnya
                    // Jika bulan menjadi 13 (Desember ke Januari), reset ke 01
                    if ($bulan_awal == 13) {
                        $bulan_awal = "1";
                    }
                } else {
                    $bulan_awal = date_format($startDate, "m"); // Bulan tetap
                }

                $bulan_akhir = 12;
                $bulan = $bulan_akhir - $bulan_awal + 1;

                foreach ($plafond_list as $plafond_lists) {
                    $balance = 0;

                    $existingHealthPlan = HealthPlan::where(
                        "employee_id",
                        $employee->employee_id,
                    )
                        ->where("period", $currentYear)
                        ->where("medical_type", $plafond_lists->medical_type)
                        ->first();

                    if (!$existingHealthPlan) {
                        if ($plafond_lists->medical_type == "Maternity") {
                            // $balance = $plafond_lists->balance * ($bulan / 12);
                            $balance = $plafond_lists->balance;
                        } elseif ($plafond_lists->medical_type == "Inpatient") {
                            $balance = $plafond_lists->balance * ($bulan / 12);
                        } elseif (
                            $plafond_lists->medical_type == "Outpatient"
                        ) {
                            $balance = $plafond_lists->balance * ($bulan / 12);
                        } elseif ($plafond_lists->medical_type == "Glasses") {
                            // $balance = $plafond_lists->balance * ($bulan / 12);
                            $balance = $plafond_lists->balance;
                        }

                        $newHealthPlan = HealthPlan::create([
                            "plan_id" => (string) Str::uuid(),
                            "employee_id" => $employee->employee_id,
                            "medical_type" => $plafond_lists->medical_type,
                            "balance" => $balance,
                            "period" => $currentYear,
                            "created_by" => $employee_id,
                            "created_at" => now(),
                        ]);

                        if ($newHealthPlan) {
                            session()->flash("refresh", true);
                        }
                    }
                }
            }
        }

        $year = date("Y");
        $month = date("m");
        $ym = $year . "-" . $month;
        $today = date("Y-m-d");

        $count = Dependents::where("employee_id", $employee_id)
            ->whereDate("updated_at", $today)
            ->count();

        if ($count > 0) {
            // return response()->json(['message' => 'Data sudah diperbarui bulan ini.']);
        } else {
            Log::info("updateDependents method started.");

            $url =
                "https://kpncorporation.darwinbox.com/orgmasterapi/getDependentDetails";

            $data = [
                "api_key" =>
                    "ae8304c9205210085def58cc613e25725872b55a6502af24eaac1586ff5dad673ed44092bd63f938433fe84839d56951f8dc110256c2b9732d55af04d01eef41",
                "employee_number" => [$employee_id],
            ];

            $headers = [
                "Content-Type" => "application/json",
                "Authorization" =>
                    "Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh",
            ];

            try {
                Log::info("Sending request to API", [
                    "url" => $url,
                    "data" => $data,
                ]);

                $response = Http::withHeaders($headers)->post($url, $data);

                if ($response->successful()) {
                    $apiResponse = $response->json();
                    Log::info("Decoded API Response", [
                        "response" => $apiResponse,
                    ]);

                    if (
                        $apiResponse["status"] == 1 &&
                        !empty($apiResponse["data"])
                    ) {
                        $data = $apiResponse["data"];

                        foreach ($data as $dependent) {
                            Dependents::updateOrInsert(
                                ["array_id" => $dependent[2]],
                                [
                                    "id" => Str::uuid(),
                                    "employee_id" => $dependent[0],
                                    "name" => trim(
                                        $dependent[3] .
                                            "" .
                                            $dependent[4] .
                                            " " .
                                            $dependent[5],
                                    ),
                                    "array_id" => $dependent[2],
                                    "first_name" => $dependent[3],
                                    "middle_name" => $dependent[4],
                                    "last_name" => $dependent[5],
                                    "relation_type" =>
                                        $dependent[6] == "Son"
                                            ? "Child"
                                            : $dependent[6],
                                    "contact_details" => $dependent[7],
                                    "phone" => $dependent[8],
                                    "date_of_birth" => Carbon::createFromFormat(
                                        "d-m-Y",
                                        $dependent[9],
                                    )->format("Y-m-d"),
                                    "nationality" => $dependent[14],
                                    "updated_on" => Carbon::createFromFormat(
                                        "d-m-Y",
                                        $dependent[15],
                                    )->format("Y-m-d"),
                                    "jobs" => $dependent[16],
                                    "gender" => explode("/", $dependent[17])[0],
                                    "no_bpjs" => $dependent[18],
                                    "education" => $dependent[19],
                                    "updated_at" => now(),
                                ],
                            );
                        }
                        // return response()->json(['message' => 'Dependents data updated successfully.']);
                    } else {
                        // return response()->json(['error' => 'No data found in API response.'], 404);
                    }
                    // return response()->json(['message' => 'Dependents data updated successfully.']);
                } else {
                    // return response()->json(['error' => 'Failed API.'], 500);
                }
            } catch (\Exception $e) {
                Log::error("Exception occurred in updateDependents method", [
                    "error" => $e->getMessage(),
                ]);
                // return response()->json(['message' => 'An error occurred: '.$e->getMessage()], 500);
            }
        }

        $family = Dependents::orderBy("date_of_birth", "asc")
            ->where("employee_id", $employee_id)
            ->get();
        $parentLink = "Reimbursement";
        $link = "Medical";

        return view(
            "hcis.reimbursements.medical.medical",
            compact(
                "family",
                "medical_plan",
                "medical",
                "parentLink",
                "link",
                "rejectMedic",
                "employees",
                "master_medical",
                "formatted_data",
                "medicalGroup",
                "gaFullname",
            ),
        );
    }

    public function medicalForm()
    {
        $employee_id = Auth::user()->employee_id;
        $currentYear = now()->year;
        $families = Dependents::orderBy("date_of_birth", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical_type = MasterMedical::orderBy("id", "desc")
            ->where("active", "T")
            ->get();

        $isMarried = Employee::where("employee_id", $employee_id)
            ->where("marital_status", "Married")
            ->exists();

        $isProbation = Employee::where("employee_id", $employee_id)
            ->where("employee_type", "Probation")
            ->exists();

        $hasGlasses =
            HealthCoverage::where("employee_id", $employee_id)
                ->where("period", $currentYear)
                ->where("medical_type", "Glasses")
                ->where("status", "!=", "Draft")
                ->whereNull("deleted_at")
                ->count() >= 1;
        // dd($isProbation);

        $hasScalling =
            HealthCoverage::where("employee_id", $employee_id)
                ->where("period", $currentYear)
                ->where("disease", "Dental (Scalling)")
                ->count() >= 1;

        $medicalBalances = HealthPlan::where("employee_id", $employee_id)
            // ->where('period', $currentYear)
            ->get();
        $balanceData = [];
        $formattedBalanceData = [];
        foreach ($medicalBalances as $balance) {
            // Assuming `medical_type` is a property of `HealthPlan`
            $balanceData[$balance->medical_type][$balance->period] =
                $balance->balance;
            $formattedBalanceData[$balance->medical_type][$balance->period] =
                number_format($balance->balance, 0, ',', '.');
        }
        // dd($balanceData);

        $employee_name = Employee::select("fullname")
            ->where("employee_id", $employee_id)
            ->first();

        $diseases = MasterDisease::orderBy("id", "asc")
            ->where("active", "T")
            ->get();

        $medical_hospitals = MedicalHospital::where("is_active", 1)->pluck("hospital_name");
        $balancePeriod = $balance->period;

        $parentLink = "Medical";
        $link = "Add Medical Coverage Usage";

        return view(
            "hcis.reimbursements.medical.form.medicalForm",
            compact(
                "diseases",
                "medical_type",
                "families",
                "parentLink",
                "link",
                "employee_name",
                "balanceData",
                "hasGlasses",
                "isMarried",
                "isProbation",
                "hasScalling",
                "medical_hospitals",
                "balancePeriod",
                "formattedBalanceData"
            ),
        );
    }

    public function medicalCreate(Request $request)
    {
        $userId = Auth::id();
        $employee_id = Auth::user()->employee_id;
        $no_medic = $this->generateNoMedic();

        $today = Carbon::today();
        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath);
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        $contribution_level_code = Employee::where("employee_id", $employee_id)
            ->pluck("contribution_level_code")
            ->first();

        $statusValue = $request->has("action_draft") ? "Draft" : "Pending";
        $employee_data = Employee::where("id", $userId)->first();
        if (!$employee_data) {
            return redirect()
                ->route("medical")
                ->with("error", "Employee data not found.");
        }

        if (empty($employee_data->bank_account_number)) {
            return redirect()
                ->route("medical")
                ->with("error", "Bank account number is empty.");
        }

        if (empty($employee_data->bank_account_name) && empty($employee_data->bank_account_name_payroll)) {
            return redirect()
                ->route("medical")
                ->with("error", "Bank account name is empty.");
        }

        $docStatusValue = "Document Pending";

        if ($statusValue == "Draft") {
            $docStatusValue = "Draft";
        }

        if ($request->has("removed_medical_proof")) {
            $removedFiles = json_decode($request->removed_medical_proof, true);
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];

            // Hapus file yang ada di server
            foreach ($removedFiles as $fileToRemove) {
                if (in_array($fileToRemove, $existingFiles)) {
                    $filePath = public_path($fileToRemove);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $existingFiles = array_filter(
                        $existingFiles,
                        fn($file) => $file !== $fileToRemove,
                    );
                }
            }
        } else {
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];
        }

        // Proses file baru
        if ($request->hasFile("medical_proof")) {
            $request->validate([
                "medical_proof.*" =>
                    "required|mimes:jpeg,png,jpg,gif,pdf|max:2048",
            ]);

            foreach ($request->file("medical_proof") as $file) {
                // Generate a unique filename
                $filename = time() . "_" . $file->getClientOriginalName();

                // Define the upload path within storage (relative to storage/app/public)
                $upload_path = "uploads/proofs/" . $employee_data->employee_id;

                // Save the file to storage/app/public/{upload_path}
                $file->storeAs($upload_path, $filename, "public");

                // Add the public URL path for the stored file
                $existingFiles[] = $upload_path . "/" . $filename;
            }
        }

        // Simpan semua file yang tersisa ke database
        $medical_proof_path = json_encode(array_values($existingFiles));

        $medical_costs = $request->input("medical_costs", []);
        $date = Carbon::parse($request->date);
        $period = $date->year;

        foreach ($medical_costs as $medical_type => $cost) {
            $cost = (int) str_replace(".", "", $cost);

            $medical_plan = HealthPlan::where("employee_id", $employee_id)
                ->where("period", $period)
                ->where("medical_type", $medical_type)
                ->first();

            if (!$medical_plan) {
                continue;
            }

            $hospital_name = $request->hospital_name;

            if (!empty($request->others_hospital_name)) {
                $hospital_name = $request->others_hospital_name;
            }

            $healthCoverage = HealthCoverage::create([
                "usage_id" => (string) Str::uuid(),
                "employee_id" => $employee_id,
                "contribution_level_code" => $contribution_level_code,
                "no_medic" => $no_medic,
                "no_invoice" => $request->no_invoice,
                "hospital_name" => $hospital_name,
                "patient_name" => $request->patient_name,
                "disease" => $request->disease,
                "date" => $date,
                "coverage_detail" => $request->coverage_detail,
                "period" => $period,
                "medical_type" => $medical_type,
                "balance" => $cost,
                "created_by" => $employee_id,
                "balance_uncoverage" => 0,
                "status" => $statusValue,
                "medical_proof" => $medical_proof_path,
                "reason" => $request->reason,
                "doc_status" => $docStatusValue,
                "doc_status_previous" => $docStatusValue,
                "billing_type" => "Reimbursement",
                "elog_medical_type" => "Reimbursement",
                "elog_vendor" => "SMEDICAL"
            ]);
        }

        if ($statusValue == "Pending" && in_array(strtolower($employee_data->group_company), [
            "downstream"
        ])) {
            $plafonds = MasterPlafond::where("group_name", $employee_data->job_level)
                ->get()
                ->keyBy("medical_type");

            $healthPlans = HealthPlan::where("employee_id", $employee_data->employee_id)
                ->where("period", $period)
                ->whereNull("deleted_at")
                ->get()
                ->keyBy("medical_type");

            $healthCoverages = HealthCoverage::where("employee_id", $employee_data->employee_id)
                ->where("period", $period)
                ->where("status", "Pending")
                ->whereNull("deleted_at")
                ->get()
                ->groupBy("medical_type")
                ->map(function ($coverages) {
                    return $coverages->sum("balance");
                });

            foreach($healthCoverages as $key => $val) {
                $plafond = $plafonds[$key];
                if (!$plafond) {
                    continue;
                }

                $healthPlan = $healthPlans[$key];
                if (!$healthPlan) {
                    continue;
                }

                if ($val > $healthPlan->balance && $healthPlan->over_plafond_email_sent_date == null) {
                    $totalUsage = HealthCoverage::where("employee_id", $employee_data->employee_id)
                        ->where("period", $healthCoverage->period)
                        ->whereIn("status", ["Pending", "Done"])
                        ->where("medical_type", $healthPlan->medical_type)
                        ->whereNull("deleted_at")
                        ->sum("balance");

                    Mail::to($employee_data->email)->bcc('dali.kewara@kpn-corp.com')->queue(
                        (new MedicalOverPlafondNotification(
                            $plafond,
                            $healthPlan,
                            $employee_data,
                            $totalUsage,
                            $base64Image
                        ))->onQueue(config('queue.hcis_queue'))
                    );

                    $healthPlan->over_plafond_email_sent_date = $today;

                    $healthPlan->save();
                }
            }
        }

        return redirect()
            ->route("medical")
            ->with("success", "Medical successfully added.");
    }

    public function medicalFormUpdate($id)
    {
        $employee_id = Auth::user()->employee_id;
        $currentYear = now()->year;
        // Fetch the HealthCoverage record by ID
        $medic = HealthCoverage::findOrFail($id);
        $selected_patient = $medic->patient_name;
        $medical_type = MasterMedical::orderBy("id", "desc")
            ->where("active", "T")
            ->get();

        $isMarried = Employee::where("employee_id", $employee_id)
            ->where("marital_status", "Married")
            ->exists();

        $hasGlasses =
            HealthCoverage::where("employee_id", $employee_id)
                ->where("period", $currentYear)
                ->where("medical_type", "Glasses")
                ->where("status", "!=", "Draft")
                ->whereNull("deleted_at")
                ->count() >= 1;

        $medicalBalances = HealthPlan::where("employee_id", $employee_id)
            // ->where('period', $currentYear)
            ->get();
        $balanceData = [];
        $formattedBalanceData = [];
        foreach ($medicalBalances as $balance) {
            // Assuming `medical_type` is a property of `HealthPlan`
            $balanceData[$balance->medical_type][$balance->period] =
                $balance->balance;
            $formattedBalanceData[$balance->medical_type][$balance->period] =
                number_format($balance->balance, 0, ',', '.');
        }
        // dd($balanceData);

        // Find all records with the same no_medic (group of medical types)
        $medicGroup = HealthCoverage::where("no_medic", $medic->no_medic)
            ->where("employee_id", $employee_id)
            ->get();

        // Extract the medical types from medicGroup
        $selectedMedicalTypes = $medicGroup->pluck("medical_type")->unique();
        $balanceMapping = $medicGroup->pluck("balance", "medical_type");
        $selectedDisease = $medic->disease;

        // Fetch related data as before
        $families = Dependents::orderBy("date_of_birth", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $employee_name = Employee::select("fullname")
            ->where("employee_id", $employee_id)
            ->first();
        $diseases = MasterDisease::orderBy("id", "asc")
            ->where("active", "T")
            ->get();

        $medical_hospitals = MedicalHospital::where("is_active", 1)->pluck("hospital_name");
        $balancePeriod = $balance->period;

        $parentLink = "Medical";
        $link = "Edit Medical Coverage Usage";

        return view(
            "hcis.reimbursements.medical.form.medicalEditForm",
            compact(
                "selectedDisease",
                "balanceMapping",
                "medic",
                "medical_type",
                "diseases",
                "families",
                "parentLink",
                "link",
                "employee_name",
                "medicGroup",
                "selectedMedicalTypes",
                "balanceData",
                "hasGlasses",
                "selected_patient",
                "isMarried",
                "medical_hospitals",
                "balancePeriod",
                "formattedBalanceData"
            ),
        );
    }

    public function medicalUpdate(Request $request, $id)
    {
        $userId = Auth::id();
        $employee_id = Auth::user()->employee_id;
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        $today = Carbon::today();
        $imagePath = public_path("images/kop.jpg");
        $imageContent = file_get_contents($imagePath);
        $base64Image = "data:image/png;base64," . base64_encode($imageContent);

        if (!$existingMedical) {
            return redirect()
                ->route("medical")
                ->with("error", "Medical record not found.");
        }

        $no_medic = $existingMedical->no_medic;

        // Handle status value
        $statusValue = $request->has("action_draft") ? "Draft" : "Pending";

        $docStatusValue = "Document Pending";

        if ($statusValue == "Draft") {
            $docStatusValue = "Draft";
        }

        if ($existingMedical->is_revise && $existingMedical->doc_status_previous) {
            $docStatusValue = $existingMedical->doc_status_previous;
        }

        // Handle medical proof file upload
        $employee_data = Employee::where("id", $userId)->first();

        if ($request->has("removed_medical_proof")) {
            $removedFiles =
                json_decode($request->removed_medical_proof, true) ?? [];
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];

            if (!is_array($removedFiles)) {
                $removedFiles = [];
            }
            if (!is_array($existingFiles)) {
                $existingFiles = [];
            }

            // Hapus file yang ada di server
            foreach ($removedFiles as $fileToRemove) {
                if (in_array($fileToRemove, $existingFiles)) {
                    $filePath = public_path($fileToRemove);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $existingFiles = array_filter(
                        $existingFiles,
                        fn($file) => $file !== $fileToRemove,
                    );
                }
            }
        } else {
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];
        }

        // Proses file baru
        if ($request->hasFile("medical_proof")) {
            $request->validate([
                "medical_proof.*" =>
                    "required|mimes:jpeg,png,jpg,gif,pdf|max:2048",
            ]);

            foreach ($request->file("medical_proof") as $file) {
                $filename = time() . "_" . $file->getClientOriginalName();
                $upload_path = "uploads/proofs/" . $employee_data->employee_id;
                $full_path = public_path($upload_path);

                if (!is_dir($full_path)) {
                    mkdir($full_path, 0755, true);
                }

                $file->move($full_path, $filename);
                $existingFiles[] = $upload_path . "/" . $filename;
            }
        }

        // Simpan semua file yang tersisa ke database
        $medical_proof_path = json_encode(array_values($existingFiles));

        $medical_costs = $request->input("medical_costs", []);
        $date = Carbon::parse($request->date);
        $period = $date->year;

        // Fetch all existing health coverages for this no_medic
        $existingCoverages = HealthCoverage::where(
            "no_medic",
            $no_medic,
        )->get();

        $hospital_name = $request->hospital_name;

        if (!empty($request->others_hospital_name)) {
            $hospital_name = $request->others_hospital_name;
        }

        // Update common fields for all records with the same no_medic
        $commonUpdateData = [
            "no_invoice" => $request->no_invoice,
            "hospital_name" => $hospital_name,
            "patient_name" => $request->patient_name,
            "disease" => $request->disease,
            "date" => $date,
            "coverage_detail" => $request->coverage_detail,
            "status" => $statusValue,
            "medical_proof" =>
                $medical_proof_path ?? $existingMedical->medical_proof,
            "created_by" => $employee_id,
            "balance_verif" => null,
            "verif_by" => null,
            "verif_at" => null,
            "reject_info" => null,
            "rejected_at" => null,
            "rejected_by" => null,
            "is_revise" => false,
            "revise_info" => null,
            "reason" => $request->reason,
            "doc_status" => $docStatusValue,
            "doc_status_previous" => $docStatusValue
        ];

        if ($existingMedical->is_revise && $existingMedical->doc_status_previous) {
            $commonUpdateData = [
                "medical_proof" =>
                    $medical_proof_path ?? $existingMedical->medical_proof,
                "is_revise" => false,
                "revise_info" => null,
                "doc_status" => $docStatusValue,
                "doc_status_previous" => $docStatusValue
            ];
        }

        HealthCoverage::where("no_medic", $no_medic)->update($commonUpdateData);

        // Process each medical type
        foreach ($medical_costs as $medical_type => $cost) {
            $cost = (int) str_replace(".", "", $cost); // Clean the currency format
            Log::info("Existing cost: " . $cost);

            // Find existing coverage for this medical type
            $existingCoverage = $existingCoverages
                ->where("medical_type", $medical_type)
                ->first();

            if ($existingCoverage) {
                // Update balance for existing coverage
                $existingCoverage->update([
                    "balance" => $cost,
                    "balance_uncoverage" => 0,
                ]);
            } else {
                // Create new coverage for new medical type
                HealthCoverage::create(
                    array_merge($commonUpdateData, [
                        "usage_id" => (string) Str::uuid(),
                        "employee_id" => $employee_id,
                        "no_medic" => $no_medic,
                        "period" => $period,
                        "medical_type" => $medical_type,
                        "balance" => $cost,
                        "created_by" => $employee_id,
                        "balance_uncoverage" => 0,
                        "balance_verif" => null,
                        "verif_by" => null,
                        "reject_info" => null,
                        "rejected_at" => null,
                        "rejected_by" => null,
                        "is_revise" => false,
                        "revise_info" => null,
                        "billing_type" => "Reimbursement",
                        "elog_medical_type" => "Reimbursement",
                        "elog_vendor" => "SMEDICAL"
                    ]),
                );
            }
        }
        // Remove any coverages that are no longer present in the update

        if (!$existingMedical->is_revise) {
            $existingCoverages
                ->whereNotIn("medical_type", array_keys($medical_costs))
                ->each(function ($coverage) {
                    $coverage->delete();
                });
        }

        if ($statusValue == "Pending" && in_array(strtolower($employee_data->group_company), [
            "downstream"
        ])) {
            $plafonds = MasterPlafond::where("group_name", $employee_data->job_level)
                ->get()
                ->keyBy("medical_type");

            $healthPlans = HealthPlan::where("employee_id", $employee_data->employee_id)
                ->where("period", $period)
                ->whereNull("deleted_at")
                ->get()
                ->keyBy("medical_type");

            $healthCoverages = HealthCoverage::where("employee_id", $employee_data->employee_id)
                ->where("period", $period)
                ->where("status", "Pending")
                ->whereNull("deleted_at")
                ->get()
                ->groupBy("medical_type")
                ->map(function ($coverages) {
                    return $coverages->sum("balance");
                });

            foreach($healthCoverages as $key => $val) {
                $plafond = $plafonds[$key];
                if (!$plafond) {
                    continue;
                }

                $healthPlan = $healthPlans[$key];
                if (!$healthPlan) {
                    continue;
                }

                if ($val > $healthPlan->balance && $healthPlan->over_plafond_email_sent_date == null) {
                    $totalUsage = HealthCoverage::where("employee_id", $employee_data->employee_id)
                        ->where("period", $healthCoverage->period)
                        ->whereIn("status", ["Pending", "Done"])
                        ->where("medical_type", $healthPlan->medical_type)
                        ->whereNull("deleted_at")
                        ->sum("balance");

                    Mail::to($employee_data->email)->bcc('dali.kewara@kpn-corp.com')->queue(
                        (new MedicalOverPlafondNotification(
                            $plafond,
                            $healthPlan,
                            $employee_data,
                            $totalUsage,
                            $base64Image
                        ))->onQueue(config('queue.hcis_queue'))
                    );

                    $healthPlan->over_plafond_email_sent_date = $today;

                    $healthPlan->save();
                }
            }
        }

        return redirect()
            ->route("medical")
            ->with("success", "Medical data successfully updated.");
    }

    public function medicalDelete($id)
    {
        $medical = HealthCoverage::findOrFail($id);
        $noMedic = $medical->no_medic; // Get the no_medic value from the record
        HealthCoverage::where("no_medic", $noMedic)->delete();

        // Redirect back with a success message
        return redirect()
            ->route("medical")
            ->with("success", "Medical Draft Deleted");
    }

    public function medicalDownload($id)
    {
        $medical_data = HealthCoverage::with(["company", "employee_verified", "employee_approved", "employee_received"])->findOrFail($id);
        $medical_datas = HealthCoverage::with([
            "company",
            "employee_verified",
            "employee_approved",
            "employee_received"
        ])
        ->where("no_medic", $medical_data->no_medic)
        ->get()
        ->keyBy('medical_type');
        $medical_no = $medical_data->no_medic;
        $medical_costing_company = $medical_data->company ? $medical_data->company->contribution_level . " (" . $medical_data->contribution_level_code . ")" : "-";
        $medical_cost_center = "-";
        $medical_submit_date = $medical_data->created_at;
        $medical_formatted_submit_date = Carbon::parse($medical_submit_date)->format('d-M-y');
        $medical_claim_date = $medical_data->date;
        $medical_formatted_claim_date = Carbon::parse($medical_claim_date)->format('d-M-y');
        $medical_periode = $medical_data->period;
        $medical_patient_name = $medical_data->patient_name;
        $medical_hospital_name = $medical_data->hospital_name;
        $medical_reason = $medical_data->reason ? $medical_data->reason : "-";
        $medical_coverage_detail = $medical_data->coverage_detail;

        $employee_data = Employee::where("employee_id", $medical_data->employee_id)->first();
        $employee_id = $employee_data->employee_id;
        $employee_name = $employee_data->fullname;
        $employee_email = $employee_data->email;
        $employee_account_detail = $employee_data->bank_name . " - " . $employee_data->bank_account_number . " - " . $employee_data->bank_account_name;
        $employee_dept = $employee_data->unit;
        $employee_pt_or_location = $employee_data->contribution_level_code . " / " . $employee_data->office_area;

        $medical_details = [];
        $medical_approvals = [
            "labels" => [],
            "statuses" => [],
            "role_names" => [],
            "employee_names" => [],
            "dates" => [],
        ];

        $medical_types = MedicalType::where("active", "T")->pluck('name');
        $medical_plans = HealthPlan::orderBy("period", "desc")
            ->where("employee_id", $employee_id)
            ->where("period", $medical_periode)
            ->get()
            ->groupBy('medical_type');

        $medical_opening_balance_plafond = 0;
        $medical_total_current_claim = 0;
        $medical_closing_balance_plafond = 0;

        foreach ($medical_types as $medical_type) {
            $md = $medical_datas[$medical_type] ?? null;

            if ($md && strtolower($medical_type) == strtolower($md->medical_type)) {
                $medical_balance = $md->balance_verif && !empty($md->balance_verif) ? $md->balance_verif : $md->balance;
                $medical_plan = $medical_plans[$medical_type];
                $medical_plafond_balance = $medical_plan[0]->balance;
                $medical_closing_plafond_balance = $medical_plafond_balance - $medical_balance;
                $medical_opening_balance_plafond += $medical_plafond_balance;
                $medical_total_current_claim += $medical_balance;
                $medical_closing_balance_plafond += $medical_closing_plafond_balance;

                $medical_details[] = [
                    "type" => $medical_type,
                    "formatted_opening_balance_plafond" => number_format($medical_plafond_balance, 0, ',', '.'),
                    "formatted_total_current_claim" => number_format($medical_balance, 0, ',', '.'),
                    "formatted_closing_balance_plafond" => number_format($medical_closing_plafond_balance, 0, ',', '.')
                ];
            } else {
                $medical_details[] = [
                    "type" => $medical_type,
                    "formatted_opening_balance_plafond" => "",
                    "formatted_total_current_claim" => "",
                    "formatted_closing_balance_plafond" => ""
                ];
            }
        }

        if ($medical_data->employee_received) {
            $medical_approvals["labels"][] = "Received";
            $medical_approvals["statuses"][] = "approved";
            $medical_approvals["role_names"][] = "HC Officer";
            $medical_approvals["employee_names"][] = $medical_data->employee_received->fullname;
            $medical_approvals["dates"][] = $medical_data->doc_received_at;
        } else {
            if (in_array(strtolower($employee_data->group_company), [
                "property"
            ])) {
                $medical_approvals["labels"][] = "Received";
                $medical_approvals["statuses"][] = "";
                $medical_approvals["role_names"][] = "HC Officer";
                $medical_approvals["employee_names"][] = "Megiyanti Matande";
                $medical_approvals["dates"][] = "";
            } else {
                // $medical_approvals["labels"][] = "Received";
                // $medical_approvals["statuses"][] = "";
                // $medical_approvals["role_names"][] = "HC Officer";
                // $medical_approvals["employee_names"][] = "";
                // $medical_approvals["dates"][] = "";
            }
        }

        if ($medical_data->employee_verified) {
            $medical_approvals["labels"][] = "Verified";
            $medical_approvals["statuses"][] = "approved";
            $medical_approvals["role_names"][] = "HC Officer";
            $medical_approvals["employee_names"][] = $medical_data->employee_verified->fullname;
            $medical_approvals["dates"][] = $medical_data->verif_at;
        } else {
            if (in_array(strtolower($employee_data->group_company), [
                "property"
            ])) {
                $medical_approvals["labels"][] = "Verified";
                $medical_approvals["statuses"][] = "";
                $medical_approvals["role_names"][] = "HC Officer";
                $medical_approvals["employee_names"][] = "Megiyanti Matande";
                $medical_approvals["dates"][] = "";
            } else {
                $medical_approvals["labels"][] = "Verified";
                $medical_approvals["statuses"][] = "";
                $medical_approvals["role_names"][] = "HC Officer";
                $medical_approvals["employee_names"][] = "";
                $medical_approvals["dates"][] = "";
            }
        }

        if ($medical_data->employee_approved) {
            $medical_approvals["labels"][] = "Approved";
            $medical_approvals["statuses"][] = "approved";
            $medical_approvals["role_names"][] = "HC Director";
            $medical_approvals["employee_names"][] = $medical_data->employee_approved->fullname;
            $medical_approvals["dates"][] = $medical_data->approved_at;
        } else {
            if (in_array(strtolower($employee_data->group_company), [
                "property"
            ])) {
                $medical_approvals["labels"][] = "Approved";
                $medical_approvals["statuses"][] = "";
                $medical_approvals["role_names"][] = "HC Director";
                $medical_approvals["employee_names"][] = "Anggelina";
                $medical_approvals["dates"][] = "";
            } else {
                $medical_approvals["labels"][] = "Approved";
                $medical_approvals["statuses"][] = "";
                $medical_approvals["role_names"][] = "HC Director";
                $medical_approvals["employee_names"][] = "";
                $medical_approvals["dates"][] = "";
            }
        }

        $medical_formatted_opening_balance_plafond = number_format($medical_opening_balance_plafond, 0, ',', '.');
        $medical_formatted_total_current_claim = number_format($medical_total_current_claim, 0, ',', '.');
        $medical_formatted_closing_balance_plafond = number_format($medical_closing_balance_plafond, 0, ',', '.');

        if (in_array(strtolower($employee_data->group_company), [
            "downstream"
        ])) {
            $medical_formatted_opening_balance_plafond = '';
            $medical_formatted_closing_balance_plafond = '';
        }

        $pdf = PDF::loadView(
            "hcis.reimbursements.medical.medical_pdf",
            [
                "medical_no" => $medical_no,
                "medical_costing_company" => $medical_costing_company,
                "medical_cost_center" => $medical_cost_center,
                "medical_submit_date" => $medical_submit_date,
                "medical_formatted_submit_date" => $medical_formatted_submit_date,
                "medical_claim_date" => $medical_claim_date,
                "medical_formatted_claim_date" => $medical_formatted_claim_date,
                "medical_periode" => $medical_periode,
                "medical_patient_name" => $medical_patient_name,
                "medical_details" => $medical_details,
                "medical_approvals" => $medical_approvals,
                "medical_formatted_opening_balance_plafond" => $medical_formatted_opening_balance_plafond,
                "medical_formatted_total_current_claim" => $medical_formatted_total_current_claim,
                "medical_formatted_closing_balance_plafond" => $medical_formatted_closing_balance_plafond,
                "medical_hospital_name" => $medical_hospital_name,
                "medical_reason" => $medical_reason,
                "medical_coverage_detail" => $medical_coverage_detail,
                "employee_id" => $employee_id,
                "employee_name" => $employee_name,
                "employee_email" => $employee_email,
                "employee_account_detail" => $employee_account_detail,
                "employee_dept" => $employee_dept,
                "employee_pt_or_location" => $employee_pt_or_location,
            ],
        )
            ->setPaper("a4", "potrait")
            ->set_option("enable_php", true);

        return $pdf->stream("Medical Claim " . $medical_no . ".pdf");
    }

    // ===================================================================
    // LEGACY - diganti oleh medicalAdminTable() di bawah.
    // Disimpan utuh sebagai pembanding. Versi ini selalu gagal:
    //   SQLSTATE[42000] 1055 - ORDER BY created_at tidak ada di GROUP BY
    //   (sql_mode ONLY_FULL_GROUP_BY).
    // Selain itu tidak pernah mengirim $master_medical / $sublink /
    // $rejectMedic / $employees yang dibutuhkan view, dan mengambil
    // usage_id satu query per baris (3.190 baris). Jangan dipakai lagi.
    // ===================================================================
//    public function medicalAdminTable()
//    {
//        // Fetch all dependents, no longer filtered by employee_id
//        $family = Dependents::orderBy("date_of_birth", "desc")->get();
//        // Fetch grouped medical data for all employees
//        $medicalGroup = HealthCoverage::select(
//            "no_medic",
//            "date",
//            "period",
//            "hospital_name",
//            "patient_name",
//            "disease",
//            DB::raw(
//                'SUM(CASE WHEN medical_type = "Maternity" THEN balance ELSE 0 END) as maternity_total',
//            ),
//            DB::raw(
//                'SUM(CASE WHEN medical_type = "Inpatient" THEN balance ELSE 0 END) as inpatient_total',
//            ),
//            DB::raw(
//                'SUM(CASE WHEN medical_type = "Outpatient" THEN balance ELSE 0 END) as outpatient_total',
//            ),
//            DB::raw(
//                'SUM(CASE WHEN medical_type = "Glasses" THEN balance ELSE 0 END) as glasses_total',
//            ),
//            "status",
//            "doc_status",
//            "is_revise"
//        )
//            ->where("status", "!=", "Draft")
//            ->whereNull("verif_by")
//            ->whereNull("balance_verif")
//            ->groupBy(
//                "no_medic",
//                "date",
//                "period",
//                "hospital_name",
//                "patient_name",
//                "disease",
//                "status",
//                "doc_status",
//                "is_revise"
//            )
//            ->orderBy("created_at", "desc")
//            ->get();
//
//        // Add usage_id for each medical record without filtering by employee_id
//        $medical = $medicalGroup->map(function ($item) {
//            // Fetch the usage_id based on no_medic (for any employee)
//            $usageId = HealthCoverage::where(
//                "no_medic",
//                $item->no_medic,
//            )->value("usage_id");
//
//            // Add usage_id to the current item
//            $item->usage_id = $usageId;
//
//            return $item;
//        });
//
//        // Fetch medical plans for all employees
//        $medical_plan = HealthPlan::orderBy("period", "desc")->get();
//
//        $parentLink = "Reimbursement";
//        $link = "Medical";
//
//        return view(
//            "hcis.reimbursements.medical.admin.medicalAdmin",
//            compact("family", "medical_plan", "medical", "parentLink", "link"),
//        );
//    }

    public function medicalAdminTable()
    {
        // $family dan $medical_plan tidak dirender di route ini:
        // familyDataAdmin / plafonMedicalAdmin hanya dipanggil di dalam
        // @if (request()->routeIs('medical.detail')) pada medicalAdmin.blade.php.
        // Tetap dikirim supaya bentuk compact() di bawah tidak berubah.
        $family = collect();
        $medical_plan = collect();
        $formatted_data = [];

        // Baris tabel diambil per halaman lewat medicalAdminTableData()
        // (DataTables server-side), jadi halaman ini tidak lagi merender 3.190
        // baris sekaligus. Query-nya sendiri ada di adminTableGroupedQuery().
        $medical = collect();
        $medicalGroup = collect();

        // Variabel di bawah dibutuhkan medicalAdmin.blade.php /
        // historyMedicalAdmin.blade.php tetapi tidak pernah dikirim versi lama,
        // sehingga render tetap gagal walau query-nya sudah benar.
        $master_medical = MasterMedical::where("active", "T")->get();

        // Berbeda dengan /medical/confirmation, query di sini tidak mengecuali
        // status 'Rejected', jadi showRejectInfo() memang bisa terpakai.
        $rejectMedic = HealthCoverage::where("status", "Rejected")
            ->get()
            ->keyBy("no_medic");

        $employeeIds = $rejectMedic
            ->pluck("employee_id")
            ->merge($rejectMedic->pluck("rejected_by"))
            ->unique();
        $employees = Employee::whereIn("employee_id", $employeeIds)->pluck(
            "fullname",
            "employee_id",
        );

        $rejectMedic->transform(function ($item) use ($employees) {
            $item->employee_fullname = $employees->get($item->employee_id);
            $item->rejected_by_fullname = $employees->get($item->rejected_by);

            return $item;
        });

        $parentLink = "Reimbursement";
        $sublink = "Medical";
        $link = "Medical";

        return view(
            "hcis.reimbursements.medical.admin.medicalAdmin",
            compact(
                "family",
                "medical_plan",
                "medical",
                "parentLink",
                "sublink",
                "link",
                "rejectMedic",
                "employees",
                "master_medical",
                "formatted_data",
            ),
        );
    }

    public function medicalAdminForm($id)
    {
        // Fetch the HealthCoverage record by ID
        $medic = HealthCoverage::findOrFail($id);
        $currentYear = now()->year;
        $medical_type = MasterMedical::orderBy("id", "desc")->get();

        // Find all records with the same no_medic (group of medical types)
        $medicGroup = HealthCoverage::where(
            "no_medic",
            $medic->no_medic,
        )->get();

        // Extract the medical types from medicGroup
        $selectedMedicalTypes = $medicGroup->pluck("medical_type")->unique();
        $balanceMapping = $medicGroup->pluck("balance", "medical_type");
        $selectedDisease = $medic->disease;

        $medicalBalances = HealthPlan::where("employee_id", $medic->employee_id)
            // ->where('period', $currentYear)
            ->get();
        $balanceData = [];
        foreach ($medicalBalances as $balance) {
            // Assuming `medical_type` is a property of `HealthPlan`
            $balanceData[$balance->medical_type][$balance->period] =
                $balance->balance;
        }

        // Fetch related data as before
        $families = Dependents::orderBy("date_of_birth", "desc")->get();
        $employee_name = Employee::select("fullname")->first();
        $diseases = MasterDisease::orderBy("id", "asc")
            ->where("active", "T")
            ->get();

        $parentLink = "Medical (Admin)";
        $link = "Medical Details";
        $key = "key";

        return view(
            "hcis.reimbursements.medical.admin.medicalAdminForm",
            compact(
                "balanceData",
                "key",
                "selectedDisease",
                "balanceMapping",
                "medic",
                "medical_type",
                "diseases",
                "families",
                "parentLink",
                "link",
                "employee_name",
                "medicGroup",
                "selectedMedicalTypes",
            ),
        );
    }

    public function medicalAdminUpdate(Request $request, $id)
    {
        $employee_id = Auth::user()->employee_id;
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        if (!$existingMedical) {
            return redirect()
                ->route("medical.admin")
                ->with("error", "Medical record not found.");
        }

        $no_medic = $existingMedical->no_medic;

        // Handle medical proof file upload (if needed)
        $medical_proof_path = null;
        if ($request->hasFile("medical_proof")) {
            $file = $request->file("medical_proof");
            $medical_proof_path = $file->store("public/storage/proofs");
        }

        // Process the medical verification costs
        $medical_costs = $request->input("medical_costs", []);
        $bpjs_costs = $request->input("bpjs_cover", []);
        $employeeCoveredAmounts = $request->input("employee_covered_amount", []);
        $companyCoveredAmounts = $request->input("company_covered_amount", []);
        $existingCoverages = HealthCoverage::where(
            "no_medic",
            $no_medic,
        )->get();
        $medicalEmployee = HealthCoverage::with(['employee'])->where(
            "no_medic",
            $no_medic,
        )->first();

        // Update the medical proof and common fields (if needed)
        $commonUpdateData = [
            "medical_proof" =>
                $medical_proof_path ?? $existingMedical->medical_proof,
            "admin_notes" => $request->input(
                "admin_notes",
                $existingMedical->admin_notes,
            ),
        ];

        HealthCoverage::where("no_medic", $no_medic)->update($commonUpdateData);

        // Process each medical type and update balance_verif
        foreach ($medical_costs as $medical_type => $verif_cost) {
            $verif_cost = (int) str_replace(".", "", $verif_cost);
            $date = Carbon::parse($request->date);
            $period = $date->year;

            $medical_plan = HealthPlan::where(
                "employee_id",
                $medicalEmployee->employee_id,
            )
                ->where("period", $period)
                ->where("medical_type", $medical_type)
                ->first();

            // dd($medical_plan);

            if (!$medical_plan) {
                continue;
            }

            // Find existing coverage for this medical type
            $existingCoverage = $existingCoverages
                ->where("medical_type", $medical_type)
                ->first();

            if ($existingCoverage) {
                $existingCoverageEmployee = Employee::where("employee_id", $existingCoverage->employee_id)->first();
                if (!$existingCoverageEmployee) {
                    return redirect()
                        ->route("medical.confirmation")
                        ->with("error", "Employee data not found");
                }

                $bpjs_cost = isset($bpjs_costs[$medical_type])
                    ? (int) str_replace(".", "", $bpjs_costs[$medical_type])
                    : 0;

                $employeeCoveredAmount = isset($employeeCoveredAmounts[$medical_type])
                    ? (int) str_replace(".", "", $employeeCoveredAmounts[$medical_type])
                    : null;
                $companyCoveredAmount = isset($companyCoveredAmounts[$medical_type])
                ? (int) str_replace(".", "", $companyCoveredAmounts[$medical_type])
                    : null;

                $docReceivedBy = $existingCoverage->doc_received_by;
                $docReceivedAt = $existingCoverage->doc_received_at;

                if (empty($docReceivedBy)) {
                    $docReceivedBy = $employee_id;
                }

                if (empty($docReceivedAt)) {
                    $docReceivedAt = Carbon::now();
                }

                if (in_array(strtolower($existingCoverageEmployee->group_company), [
                    "downstream",
                    "plantations",
                    "kpn plantations"
                ])) {
                    $deductionCost = $verif_cost;

                    if ($companyCoveredAmount !== null) {
                        $deductionCost = $companyCoveredAmount;
                    }

                    if ($deductionCost > $medical_plan->balance) {
                        return redirect()
                            ->route("medical.confirmation")
                            ->with("error", "Medical plafond exceded");
                    }

                    $medical_plan->balance -= $deductionCost;

                    $medical_plan->save();
                }

                $existingCoverage->update([
                    "balance_verif" => $verif_cost,
                    "verif_by" => $employee_id,
                    "verif_at" => Carbon::now(),
                    "status" => "Pending",
                    "doc_status" => "Claim Verified",
                    "doc_status_previous" => "Document Received",
                    "doc_received_by" => $docReceivedBy,
                    "doc_received_at" => $docReceivedAt,
                    "balance_bpjs" => $bpjs_cost,
                    "is_revise" => false,
                    "revise_info" => null,
                    "employee_covered_amount" => $employeeCoveredAmount,
                    "company_covered_amount" => $companyCoveredAmount
                ]);
                if ($medical_plan->balance < $verif_cost) {
                    $old_balance_total =
                        $medical_plan->balance + $existingCoverage->balance; // Combine balances
                    $balance_diff = $old_balance_total - $verif_cost;
                    $balance_diff_formatted = abs($balance_diff);

                    $existingCoverage->update([
                        "balance_uncoverage" => $balance_diff_formatted,
                    ]);
                }
            } else {
                Log::info(
                    "No existing coverage found for medical_type: $medical_type",
                );
            }
        }

        return redirect()
            ->route("medical.confirmation")
            ->with(
                "success",
                "Medical verification data successfully updated.",
            );
    }

    public function medicalAdminUpdateRevise(Request $request, $id)
    {
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        if (!$existingMedical) {
            return redirect()
                ->route("medical.admin")
                ->with("error", "Medical record not found.");
        }

        $existingMedicals = HealthCoverage::where("no_medic", $existingMedical->no_medic)->get();

        $docStatus = "Pending";
        $docStatusPrevious = $existingMedical->doc_status;

        foreach ($existingMedicals as $emd) {
            if ($docStatusPrevious == "Pending") {
                $emd->update([
                    "doc_status" => $docStatus,
                    "is_revise" => true,
                    "revise_info" => $request->revise_info,
                ]);
            } else {
                $emd->update([
                    "doc_status" => $docStatus,
                    "doc_status_previous" => $docStatusPrevious,
                    "is_revise" => true,
                    "revise_info" => $request->revise_info,
                ]);
            }
        }

        return redirect()
            ->route("medical.confirmation")
            ->with(
                "success",
                "Medical verification data successfully updated.",
            );
    }

    public function medicalApprovalUpdateRevise(Request $request, $id)
    {
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        if (!$existingMedical) {
            return redirect()
                ->route("medical.approval")
                ->with("error", "Medical record not found.");
        }

        $existingMedicals = HealthCoverage::where("no_medic", $existingMedical->no_medic)->get();

        $docStatus = "Pending";
        $docStatusPrevious = $existingMedical->doc_status;

        foreach ($existingMedicals as $emd) {
            if ($docStatusPrevious == "Pending") {
                $emd->update([
                    "doc_status" => $docStatus,
                    "is_revise" => true,
                    "revise_info" => $request->revise_info,
                ]);
            } else {
                $emd->update([
                    "doc_status" => $docStatus,
                    "doc_status_previous" => $docStatusPrevious,
                    "is_revise" => true,
                    "revise_info" => $request->revise_info,
                ]);
            }
        }

        return redirect()
            ->route("medical.approval")
            ->with(
                "success",
                "Medical verification data successfully updated.",
            );
    }

    public function medicalAdminUpdateReject(Request $request, $id)
    {
        $employee_id = Auth::user()->employee_id;
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        if (!$existingMedical) {
            return redirect()
                ->route("medical.admin")
                ->with("error", "Medical record not found.");
        }

        $existingMedicals = HealthCoverage::where("no_medic", $existingMedical->no_medic)->get();

        $status = "Rejected";
        $docStatus = "Rejected";
        $docStatusPrevious = $existingMedical->doc_status;

        $formattedDocReceivedAt = null;

        foreach ($existingMedicals as $emd) {
            $emd->update([
                "status" => $status,
                "doc_status" => $docStatus,
                "doc_status_previous" => $docStatusPrevious,
                "is_revise" => false,
                "revise_info" => null,
                "reject_info" => $request->reject_info,
                "rejected_by" => $employee_id,
                "rejected_at" => now(),
            ]);
        }

        return redirect()
            ->route("medical.confirmation")
            ->with(
                "success",
                "Medical verification data successfully updated.",
            );
    }

    public function medicalAdminUpdateDocumentReceivedToggle($id)
    {
        $employee_id = Auth::user()->employee_id;
        $existingMedical = HealthCoverage::where("usage_id", $id)->first();

        if (!$existingMedical) {
            return response()->json([
                'success' => false,
                'message' => 'Medical record not found.'
            ], 404);
        }

        $existingMedicals = HealthCoverage::where("no_medic", $existingMedical->no_medic)->get();

        $docStatus = $existingMedical->doc_status;
        $docStatusPrevious = $existingMedical->doc_status_previous;

        if (empty($docStatus) || $docStatus == "Pending" || $docStatus == "Document Pending" ||
        $docStatus == "Draft" || $docStatus == "Revise" || $docStatus == "Rejected") {
            $docStatusPrevious = $docStatus;
            $docStatus = "Document Received";

            if (empty($docStatus)) {
                $docStatusPrevious = "Document Pending";
            }
        } else if ($docStatus == "Document Received") {
            $docStatusPrevious = $docStatus;
            $docStatus = "Document Pending";
        }

        $formattedDocReceivedAt = null;

        foreach ($existingMedicals as $emd) {
            if ($docStatus == "Document Received") {
                $emd->update([
                    "doc_status" => $docStatus,
                    "doc_status_previous" => $docStatusPrevious,
                    "doc_received_by" => $employee_id,
                    "doc_received_at" => Carbon::now(),
                    "is_revise" => false,
                    "revise_info" => null,
                ]);

                $formattedDocReceivedAt = $emd->doc_received_at
                    ->setTimezone('Asia/Jakarta')
                    ->format('Y-m-d H:i:s');
            } else if ($docStatus == "Document Pending") {
                $emd->update([
                    "doc_status" => $docStatus,
                    "doc_status_previous" => $docStatusPrevious,
                    "doc_received_by" => null,
                    "doc_received_at" => null,
                    "is_revise" => false,
                    "revise_info" => null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Medical verification data successfully updated.',
            'data' => [
                'doc_status' => $docStatus,
                'doc_received_at' => $formattedDocReceivedAt,
            ]
        ]);
    }

    public function medicalAdminDelete($id)
    {
        $medical = HealthCoverage::findOrFail($id);

        if ($medical->status == "Done") {
            // Ambil nilai yang diperlukan dari record
            $noMedic = $medical->no_medic;
            $balanceVerif = $medical->balance_verif; // Misalnya: 2,000,000
            $balanceUncoverage = $medical->balance_uncoverage; // Misalnya: 1,500,000
            $employeeId = $medical->employee_id;
            $period = $medical->period;
            $medicalType = $medical->medical_type;
            // dd($period);

            // Hitung hasil pengurangan balance_verif dengan balance_uncoverage
            // $adjustmentValue = $balanceVerif - $balanceUncoverage; // 2,000,000 - 1,500,000 = 500,000

            // Cari data HealthPlan berdasarkan kriteria
            $healthPlan = HealthPlan::where("employee_id", $employeeId)
                ->where("period", $period)
                ->where("medical_type", $medicalType)
                ->first();
            // dd($healthPlan->balance += $balanceVerif);

            if ($healthPlan) {
                // Tambahkan hasil pengurangan ke balance HealthPlan
                $healthPlan->balance += $balanceVerif; // 60,000,000 + 722,231 = 60,722,231
                $healthPlan->save(); // Simpan perubahan ke database
            }
        }

        // Soft delete pada data HealthCoverage
        $medical->delete();

        // Redirect back with a success message
        return redirect()->back()->with("success", "Medical Deleted");
    }

    public function medicalReportAdminDelete($id)
    {
        // Ambil data HealthCoverage berdasarkan ID
        $medical = HealthCoverage::findOrFail($id);

        if ($medical->status == "Done") {
            // Ambil nilai yang diperlukan dari record
            $noMedic = $medical->no_medic;
            $balanceVerif = $medical->balance_verif; // Misalnya: 999,999
            $balanceUncoverage = $medical->balance_uncoverage; // Misalnya: 277,768
            $employeeId = $medical->employee_id;
            $period = $medical->period;
            $medicalType = $medical->medical_type;

            // Hitung hasil pengurangan balance_verif dengan balance_uncoverage
            // $adjustmentValue = $balanceVerif - $balanceUncoverage; // 999,999 - 277,768 = 722,231

            // Cari data HealthPlan berdasarkan kriteria
            $healthPlan = HealthPlan::where("employee_id", $employeeId)
                ->where("period", $period)
                ->where("medical_type", $medicalType)
                ->first();

            if ($healthPlan) {
                // Tambahkan hasil pengurangan ke balance HealthPlan
                $healthPlan->balance += $balanceVerif; // 60,000,000 + 722,231 = 60,722,231
                $healthPlan->save(); // Simpan perubahan ke database
            }
        }

        // Soft delete pada data HealthCoverage
        $medical->delete();

        // Redirect kembali dengan pesan sukses
        return redirect()
            ->back()
            ->with(
                "success",
                "Medical record deleted and balance adjusted successfully.",
            );
    }

    public function medicalApproval()
    {
        // Fetch all dependents, no longer filtered by employee_id
        $user = Auth::user();
        $family = Dependents::orderBy("date_of_birth", "desc")->get();
        $employeeId = auth()->user()->employee_id;
        $employee = Employee::where("employee_id", $employeeId)->first();

        // Check if the user has approval rights ->where('approval_medical', $employee->employee_id)
        $hasApprovalRights = DB::table("master_bisnisunits")
            ->whereRaw("FIND_IN_SET(?, approval_medical)", [
                $employee->employee_id,
            ])
            ->where("nama_bisnis", $employee->group_company)
            ->exists();

        $accessBisnis = DB::table("master_bisnisunits")
            ->whereRaw("FIND_IN_SET(?, approval_medical)", [
                $employee->employee_id,
            ])
            ->pluck("nama_bisnis") // Ambil hanya kolom nama_bisnis
            ->toArray();
        //->where('approval_medical', $employee->employee_id)

        if ($hasApprovalRights) {
            $medicalGroup = HealthCoverage::from(
                "mdc_transactions as mdc_transactions",
            )
                ->join(
                    "employees as e",
                    "mdc_transactions.employee_id",
                    "=",
                    "e.employee_id",
                )
                ->select(
                    "mdc_transactions.no_medic",
                    "mdc_transactions.date",
                    "mdc_transactions.period",
                    "mdc_transactions.hospital_name",
                    "mdc_transactions.patient_name",
                    "mdc_transactions.disease",
                    DB::raw(
                        'SUM(CASE WHEN mdc_transactions.medical_type = "Maternity" THEN mdc_transactions.balance_verif ELSE 0 END) as maternity_balance_verif',
                    ),
                    DB::raw(
                        'SUM(CASE WHEN mdc_transactions.medical_type = "Inpatient" THEN mdc_transactions.balance_verif ELSE 0 END) as inpatient_balance_verif',
                    ),
                    DB::raw(
                        'SUM(CASE WHEN mdc_transactions.medical_type = "Outpatient" THEN mdc_transactions.balance_verif ELSE 0 END) as outpatient_balance_verif',
                    ),
                    DB::raw(
                        'SUM(CASE WHEN mdc_transactions.medical_type = "Glasses" THEN mdc_transactions.balance_verif ELSE 0 END) as glasses_balance_verif',
                    ),
                    "mdc_transactions.status",
                    "mdc_transactions.doc_status",
                    "mdc_transactions.is_revise",
                    "mdc_transactions.created_at",
                    "e.fullname",
                )
                ->whereNotNull("mdc_transactions.verif_by")
                ->whereNotNull("mdc_transactions.balance_verif")
                ->where("mdc_transactions.status", "Pending")
                ->whereIn("e.group_company", $accessBisnis)
                ->groupBy(
                    "mdc_transactions.no_medic",
                    "mdc_transactions.date",
                    "mdc_transactions.period",
                    "mdc_transactions.hospital_name",
                    "mdc_transactions.patient_name",
                    "mdc_transactions.disease",
                    "mdc_transactions.status",
                    "mdc_transactions.doc_status",
                    "mdc_transactions.is_revise",
                    "mdc_transactions.created_at",
                    "e.fullname",
                )
                ->orderBy("mdc_transactions.created_at", "desc")
                ->get();

            // Add usage_id for each medical record without filtering by employee_id
            $medical = $medicalGroup->map(function ($item) {
                // Fetch the usage_id based on no_medic (for any employee)
                $usageId = HealthCoverage::where(
                    "no_medic",
                    $item->no_medic,
                )->value("usage_id");
                $item->usage_id = $usageId;

                // Calculate total per no_medic
                $item->total_per_no_medic =
                    $item->maternity_balance_verif +
                    $item->inpatient_balance_verif +
                    $item->outpatient_balance_verif +
                    $item->glasses_balance_verif;

                return $item;
            });
        } else {
            $medical = collect(); // Empty collection if user doesn't have approval rights
        }

        $totalMDCCount = $medical->count();

        // Fetch medical plans for all employees
        $medical_plan = HealthPlan::orderBy("period", "desc")->get();
        $master_medical = MasterMedical::where("active", "T")->get();

        $totalMDCCount = $medical->count();

        $totalPendingCount = CATransaction::where(function ($query) use (
            $employeeId,
        ) {
            $query
                ->where("status_id", $employeeId)
                ->where("approval_status", "Pending")
                ->orWhere("sett_id", $employeeId)
                ->where("approval_sett", "Pending")
                ->orWhere("extend_id", $employeeId)
                ->where("approval_extend", "Pending");
        })->count();

        $totalBTCount = BusinessTrip::where(function ($query) use ($user) {
            $query
                ->where(function ($q) use ($user) {
                    $q->where("manager_l1_id", $user->employee_id)->whereIn(
                        "status",
                        ["Pending L1", "Declaration L1"],
                    );
                })
                ->orWhere(function ($q) use ($user) {
                    $q->where("manager_l2_id", $user->employee_id)->whereIn(
                        "status",
                        ["Pending L2", "Declaration L2"],
                    );
                });
        })->count();

        $hotelNumbers = Hotel::where("hotel_only", "Y")
            ->where("approval_status", "!=", "Draft")
            ->pluck("no_htl")
            ->unique();

        // Fetch all tickets using the latestTicketIds
        $transactions_htl = Hotel::whereIn("no_htl", $hotelNumbers)
            ->with("businessTrip")
            ->orderBy("created_at", "desc")
            ->get();

        // Filter tickets based on manager and approval status
        $hotels = $transactions_htl->filter(function ($hotel) use ($employee) {
            // Get the employee who owns the ticket
            $ticketOwnerEmployee = Employee::where(
                "id",
                $hotel->user_id,
            )->first();

            if (
                $hotel->approval_status == "Pending L1" &&
                $hotel->manager_l1_id == $employee->employee_id
            ) {
                return true;
            } elseif (
                $hotel->approval_status == "Pending L2" &&
                $hotel->manager_l2_id == $employee->employee_id
            ) {
                return true;
            }
            return false;
        });

        $totalHTLCount = $hotels->count();

        $ticketNumbers = Tiket::where("tkt_only", "Y")
            ->where("approval_status", "!=", "Draft")
            ->pluck("no_tkt")
            ->unique();
        $transactions_tkt = Tiket::whereIn("no_tkt", $ticketNumbers)
            ->with("businessTrip")
            ->orderBy("created_at", "desc")
            ->get();
        $totalTKTCount = $transactions_tkt
            ->filter(function ($ticket) use ($employee) {
                $ticketOwnerEmployee = Employee::where(
                    "id",
                    $ticket->user_id,
                )->first();
                return ($ticket->approval_status == "Pending L1" &&
                    $ticket->manager_l1_id == $employee->employee_id) ||
                    ($ticket->approval_status == "Pending L2" &&
                        $ticket->manager_l2_id == $employee->employee_id);
            })
            ->count();

        $parentLink = "Reimbursement";
        $link = "Medical Approval";

        return view(
            "hcis.reimbursements.medical.approval.medicalApproval",
            compact(
                "family",
                "medical_plan",
                "medical",
                "parentLink",
                "link",
                "master_medical",
                "totalBTCount",
                "totalPendingCount",
                "totalHTLCount",
                "totalTKTCount",
                "totalMDCCount",
            ),
        );
    }

    public function medicalApprovalForm($id)
    {
        // Fetch the HealthCoverage record by ID
        $medic = HealthCoverage::findOrFail($id);
        $currentYear = now()->year;
        $medical_type = MasterMedical::orderBy("id", "desc")
            ->where("active", "T")
            ->get();

        // Find all records with the same no_medic (group of medical types)
        $medicGroup = HealthCoverage::where(
            "no_medic",
            $medic->no_medic,
        )->get();

        $medicalBalances = HealthPlan::where("employee_id", $medic->employee_id)
            // ->whereYear('period', $currentYear)
            ->get();

        $balanceData = [];
        foreach ($medicalBalances as $balance) {
            // Assuming `medical_type` is a property of `HealthPlan`
            $balanceData[$balance->medical_type][$balance->period] =
                $balance->balance;
        }
        // dd($balanceData);
        // Extract the medical types from medicGroup
        $selectedMedicalTypes = $medicGroup->pluck("medical_type")->unique();
        $balanceMapping = $medicGroup->pluck("balance_verif", "medical_type");
        $balanceBPJSMapping = $medicGroup->pluck(
            "balance_bpjs",
            "medical_type",
        );
        $selectedDisease = $medic->disease;

        // Fetch related data as before
        $families = Dependents::orderBy("date_of_birth", "desc")->get();
        $employee_name = Employee::select("fullname")->first();
        $diseases = MasterDisease::orderBy("id", "asc")
            ->where("active", "T")
            ->get();

        $parentLink = "Medical Approval";
        $link = "Medical Details";

        return view(
            "hcis.reimbursements.medical.approval.medicalApprovalDetail",
            compact(
                "selectedDisease",
                "balanceMapping",
                "medic",
                "medical_type",
                "diseases",
                "families",
                "parentLink",
                "link",
                "employee_name",
                "medicGroup",
                "selectedMedicalTypes",
                "balanceData",
                "balanceBPJSMapping",
            ),
        );
    }

    public function medicalApprovalUpdate($id, Request $request)
    {
        // Find the medical record by ID
        $employee_id = Auth::user()->employee_id;
        $medical = HealthCoverage::findOrFail($id);

        // Determine the new status based on the action
        $action = $request->input("status_approval");
        $rejectInfo = $request->input("reject_info");

        if ($action == "Rejected") {
            $statusValue = "Rejected";

            // Fetch all records with the same 'no_medic'
            $healthCoverages = HealthCoverage::where(
                "no_medic",
                $medical->no_medic,
            )->get();

            // Loop through each health coverage record and update accordingly
            foreach ($healthCoverages as $coverage) {
                // Update the health coverage record to reflect rejection
                $coverage->update([
                    "status" => $statusValue,
                    "doc_status" => $statusValue,
                    "doc_status_previous" => $coverage->doc_status,
                    "reject_info" => $rejectInfo,
                    "rejected_by" => $employee_id,
                    "rejected_at" => now(),
                    "is_revise" => false,
                    "revise_info" => null,
                ]);
            }

            return redirect()
                ->route("medical.approval")
                ->with(
                    "success",
                    "Medical request rejected and balances updated.",
                );
        } elseif ($action == "Done") {
            $statusValue = "Done";

            // Fetch all records with the same 'no_medic'
            $healthCoverages = HealthCoverage::where(
                "no_medic",
                $medical->no_medic,
            )->get();

            // Loop through each health coverage record and update accordingly
            foreach ($healthCoverages as $coverage) {
                $medicalType = $coverage->medical_type;
                $balanceVerif = $coverage->balance_verif;
                $companyCoveredAmount = $coverage->company_covered_amount;
                $employeeId = $coverage->employee_id;
                $date = Carbon::parse($request->date);
                $period = $date->year;

                $employee = Employee::where("employee_id", $employeeId)->first();
                if (!$employee) {
                    return redirect()
                        ->route("medical")
                        ->with("error", "Employee data not found.");
                }

                $healthPlan = HealthPlan::where("employee_id", $employeeId)
                    ->where("medical_type", $medicalType)
                    ->where("period", $coverage->period) //$period
                    ->first();
                if (!$healthPlan) {
                    return redirect()
                        ->route("medical")
                        ->with("error", "Medical Plafond data not found.");
                }

                if (in_array(strtolower($employee->group_company), [
                    "downstream",
                    "plantations",
                    "kpn plantations"
                ])) {
                    // PLAFOND DEDUCTION HAPPEND ON VERIFY PROCESS
                    // if ($healthPlan->balance <= 0) {
                    //     return redirect()
                    //         ->route("medical")
                    //         ->with("error", "Medical plafond exceded");
                    // }
                    //
                    // if ($companyCoveredAmount !== null) {
                    //     $healthPlan->balance -= $companyCoveredAmount;
                    // } else {
                    //     $healthPlan->balance -= $balanceVerif;
                    // }
                } else {
                    $healthPlan->balance -= $balanceVerif;

                    $healthPlan->save();
                }

                $coverage->update([
                    "status" => $statusValue,
                    "doc_status" => "Reimbursement Approved",
                    "doc_status_previous" => $coverage->doc_status,
                    "approved_by" => $employee_id,
                    "approved_at" => now(),
                    "is_revise" => false,
                    "revise_info" => null,
                ]);

                ELogInsertFirstReceiptJob::dispatch($coverage->usage_id);
            }

            return redirect()
                ->route("medical.approval")
                ->with(
                    "success",
                    "Medical request approved and balances updated.",
                );
        }
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
            // Extract the last 6 digits (the sequence part) and increment it by 1
            $lastNumber = (int) substr($lastCoverage->no_medic, 4); // Extract the last 6 digits
            $nextNumber = $lastNumber + 1;
        } else {
            // If no records for this year or no records at all, start from 000001
            $nextNumber = 1;
        }

        // Format the next number as a 9-digit number starting with '6'
        $newNoMedic =
            "MD" . $currentYear . str_pad($nextNumber, 6, "0", STR_PAD_LEFT);

        return $newNoMedic;
    }

    public function medicalAdmin(Request $request)
    {
        $parentLink = "Reimbursement";
        $link = "Medical Data Employee";
        $userId = Auth::id();
        $companies = Company::orderBy("contribution_level")->get();

        $userRole = auth()->user()->roles->first();
        $roleRestriction = json_decode($userRole->restriction, true);
        $restrictedWorkAreas = $roleRestriction["work_area_code"] ?? [];
        $restrictedGroupCompanies = $roleRestriction["group_company"] ?? [];

        if (!empty($restrictedWorkAreas)) {
            $locations = Location::orderBy("area")
                ->whereIn("work_area", $restrictedWorkAreas)
                ->get();
        } else {
            $locations = Location::orderBy("area")->get();
        }

        $currentYear = date("Y");

        $query = Employee::with([
            "employee",
            "statusReqEmployee",
            "statusSettEmployee",
        ]);

        $med_employee = collect();
        $hasFilter = false;

        if (request()->get("stat") == "") {
        } else {
            if ($request->has("stat") && $request->input("stat") !== "") {
                $status = $request->input("stat");
                $query->where("work_area_code", $status);
                $hasFilter = true;
            }
        }

        if (request()->get("customsearch") == "") {
        } else {
            if (
                $request->has("customsearch") &&
                $request->input("customsearch") !== ""
            ) {
                $customsearch = $request->input("customsearch");
                if (!empty($restrictedWorkAreas)) {
                    $query
                        ->where("fullname", "LIKE", "%" . $customsearch . "%")
                        ->where("work_area_code", $restrictedWorkAreas);
                } else {
                    $query->where(
                        "fullname",
                        "LIKE",
                        "%" . $customsearch . "%",
                    );
                }
                $hasFilter = true;
            }
        }

        // Hanya jalankan query jika ada salah satu filter
        if ($hasFilter) {
            $med_employee = $query->orderBy("created_at", "desc")->get();
        }

        $medical_plans = HealthPlan::where("period", $currentYear)->get();

        $balances = [];
        foreach ($medical_plans as $plan) {
            $balances[$plan->employee_id][$plan->medical_type] = $plan->balance;
        }

        foreach ($med_employee as $transaction) {
            $transaction->ReqName = $transaction->statusReqEmployee
                ? $transaction->statusReqEmployee->fullname
                : "";
            $transaction->settName = $transaction->statusSettEmployee
                ? $transaction->statusSettEmployee->fullname
                : "";

            $employeeMedicalPlan = $medical_plans
                ->where("employee_id", $transaction->employee_id)
                ->first();
            $transaction->period = $employeeMedicalPlan
                ? $employeeMedicalPlan->period
                : "-";
        }

        return view("hcis.reimbursements.medical.adminMedical", [
            "link" => $link,
            "parentLink" => $parentLink,
            "userId" => $userId,
            "med_employee" => $med_employee,
            "companies" => $companies,
            "locations" => $locations,
            "master_medical" => MasterMedical::where("active", "T")->get(),
            "balances" => $balances, // Kirim balances ke view
        ]);
    }

    // ===================================================================
    // LEGACY - diganti oleh medicalReportAdmin() di bawah.
    // Disimpan utuh sebagai pembanding. Versi ini:
    //  - memuat SELURUH mdc_transactions (43.472 baris model Eloquent)
    //    ke memori lalu groupBy di PHP, walau tabelnya tidak dirender
    //    sama sekali ketika belum ada filter (~1,2 dtk, 246 MB);
    //  - memuat mdc_plans tahun berjalan untuk membangun $balances yang
    //    tidak pernah dipakai view;
    //  - eager-load tiga relasi Employee yang tidak pernah dibaca view.
    // Jangan dipakai lagi.
    // ===================================================================
//    public function medicalReportAdmin(Request $request)
//    {
//        ini_set("max_execution_time", 300);
//
//        $parentLink = "Reimbursement";
//        $link = "Medical Data Employee";
//        $userId = Auth::id();
//        $companies = Company::orderBy("contribution_level")->get();
//
//        $currentYear = date("Y");
//
//        $med_employee = collect();
//        $hasFilter = false;
//        $medicalGroup = [];
//
//        $userRole = auth()->user()->roles->last();
//        $roleRestriction = json_decode($userRole->restriction, true);
//        $restrictedWorkAreas = $roleRestriction["work_area_code"] ?? [];
//        $restrictedGroupCompanies = $roleRestriction["group_company"] ?? [];
//
//        $healthCoverageQuery = HealthCoverage::query();
//
//        // Filter berdasarkan start_date dan end_date hanya untuk HealthCoverage
//        if (request()->get("start_date") == "") {
//        } else {
//            if (
//                $request->has(["start_date", "end_date"]) &&
//                $request->input("start_date") != "" &&
//                $request->input("end_date") != ""
//            ) {
//                $startDate = $request->input("start_date");
//                $endDate = Carbon::parse($request->input("end_date"))->addDay();
//                $healthCoverageQuery->whereBetween("created_at", [
//                    $startDate,
//                    $endDate,
//                ]);
//                $hasFilter = true;
//            }
//        }
//
//        if (request()->get("statusMDC") == "") {
//        } else {
//            if (
//                $request->has("statusMDC") &&
//                $request->input("statusMDC") !== ""
//            ) {
//                $statusMDC = $request->input("statusMDC");
//                $healthCoverageQuery->where("status", $statusMDC);
//                $hasFilter = true;
//            }
//        }
//
//        $medicalGroup = $healthCoverageQuery->get()->groupBy("employee_id");
//
//        $query = Employee::with([
//            "employee",
//            "statusReqEmployee",
//            "statusSettEmployee",
//        ]);
//
//        if (request()->get("stat") == "") {
//        } else {
//            if ($request->has("stat") && $request->input("stat") !== "") {
//                $status = $request->input("stat");
//                $query->where("group_company", $status);
//                $hasFilter = true;
//            }
//        }
//
//        if (request()->get("unit") == "") {
//        } else {
//            if ($request->has("unit") && $request->input("unit") !== "") {
//                $unit = $request->input("unit");
//                $query->where("work_area_code", $unit);
//                $hasFilter = true;
//            }
//        }
//
//        if (request()->get("customsearch") == "") {
//        } else {
//            if (
//                $request->has("customsearch") &&
//                $request->input("customsearch") !== ""
//            ) {
//                $customsearch = $request->input("customsearch");
//                $query->where("fullname", "LIKE", "%" . $customsearch . "%");
//                $hasFilter = true;
//            }
//        }
//
//        if (!empty($restrictedWorkAreas)) {
//            // Tambahkan filter whereIn pada work_area_code jika ada restriction
//            $query->whereIn("work_area_code", $restrictedWorkAreas);
//            // dd($restrictedWorkAreas);
//            $locations = Location::orderBy("area")
//                ->whereIn("work_area", $restrictedWorkAreas)
//                ->get();
//        } else {
//            $locations = Location::orderBy("area")->get();
//        }
//
//        if (!empty($restrictedGroupCompanies)) {
//            $unit = MasterBusinessUnit::whereIn(
//                "nama_bisnis",
//                $restrictedGroupCompanies,
//            )->get();
//        } else {
//            $unit = MasterBusinessUnit::get(); // Jika tidak ada restriction, ambil semua
//        }
//
//        if ($hasFilter) {
//            $med_employee = $query->orderBy("created_at", "desc")->get();
//        }
//
//        $medical_plans = HealthPlan::where("period", $currentYear)->get();
//
//        $balances = [];
//        foreach ($medical_plans as $plan) {
//            $balances[$plan->employee_id][$plan->medical_type] = $plan->balance;
//        }
//
//        // foreach ($med_employee as $transaction) {
//        //     $transaction->ReqName = $transaction->statusReqEmployee ? $transaction->statusReqEmployee->fullname : '';
//        //     $transaction->settName = $transaction->statusSettEmployee ? $transaction->statusSettEmployee->fullname : '';
//
//        //     $employeeMedicalPlan = $medical_plans->where('employee_id', $transaction->employee_id)->first();
//        //     $transaction->period = $employeeMedicalPlan ? $employeeMedicalPlan->period : '-';
//
//        //     if (isset($medicalGroup[$transaction->employee_id])) {
//        //         $transaction->medical_coverage = $medicalGroup[$transaction->employee_id];
//        //     }
//        // }
//
//        return view("hcis.reimbursements.medical.admin.reportMedicalAdmin", [
//            "link" => $link,
//            "parentLink" => $parentLink,
//            "userId" => $userId,
//            "med_employee" => $med_employee,
//            "companies" => $companies,
//            "locations" => $locations,
//            "master_medical" => MasterMedical::where("active", "T")->get(),
//            "balances" => $balances, // Kirim balances ke view
//            "unit" => $unit,
//            "medicalGroup" => $medicalGroup,
//            "userRole" => $userRole,
//            "roleRestriction" => $roleRestriction,
//            "restrictedWorkAreas" => $restrictedWorkAreas,
//        ]);
//    }

    public function medicalReportAdmin(Request $request)
    {
        $parentLink = "Reimbursement";
        $link = "Medical Data Employee";
        $userId = Auth::id();
        $companies = Company::orderBy("contribution_level")->get();

        $userRole = auth()->user()->roles->last();
        $roleRestriction = json_decode($userRole->restriction, true);
        $restrictedWorkAreas = $roleRestriction["work_area_code"] ?? [];
        $restrictedGroupCompanies = $roleRestriction["group_company"] ?? [];

        if (!empty($restrictedWorkAreas)) {
            $locations = Location::orderBy("area")
                ->whereIn("work_area", $restrictedWorkAreas)
                ->get();
        } else {
            $locations = Location::orderBy("area")->get();
        }

        if (!empty($restrictedGroupCompanies)) {
            $unit = MasterBusinessUnit::whereIn(
                "nama_bisnis",
                $restrictedGroupCompanies,
            )->get();
        } else {
            $unit = MasterBusinessUnit::get(); // Jika tidak ada restriction, ambil semua
        }

        // Baris tabel diambil per halaman lewat medicalReportAdminData()
        // (DataTables server-side). Versi sebelumnya merender 12.590 baris
        // sekaligus (HTML ~53 MB) dan memuat seluruh mdc_transactions ke
        // memori. Keduanya dikirim kosong supaya kontrak view tidak berubah.
        $med_employee = collect();
        $medicalGroup = [];

        return view("hcis.reimbursements.medical.admin.reportMedicalAdmin", [
            "link" => $link,
            "parentLink" => $parentLink,
            "userId" => $userId,
            "med_employee" => $med_employee,
            "companies" => $companies,
            "locations" => $locations,
            "master_medical" => MasterMedical::where("active", "T")->get(),
            // $balances tidak pernah dipakai view.
            "balances" => [],
            "unit" => $unit,
            "medicalGroup" => $medicalGroup,
            "userRole" => $userRole,
            "roleRestriction" => $roleRestriction,
            "restrictedWorkAreas" => $restrictedWorkAreas,
        ]);
    }

    /**
     * Query dasar tabel "Medical Data Employee".
     *
     * Versi lama membangun tabel dari dua loop bersarang di Blade (karyawan x
     * transaksinya). Di sini hubungan itu dinyatakan sebagai JOIN supaya bisa
     * di-page di sisi server.
     *
     * Mengembalikan [builder, hasFilter]. Sama seperti versi lama, tanpa satu
     * pun filter tabelnya memang tidak menampilkan baris.
     */
    private function medicalReportJoinedQuery(Request $request): array
    {
        $roleRestriction = json_decode(
            auth()->user()->roles->last()->restriction,
            true,
        );
        $restrictedWorkAreas = $roleRestriction["work_area_code"] ?? [];

        $hasFilter = false;

        $query = Employee::query()
            ->join("mdc_transactions", function ($join) {
                $join
                    ->on(
                        "mdc_transactions.employee_id",
                        "=",
                        "employees.employee_id",
                    )
                    // HealthCoverage memakai SoftDeletes; join manual harus
                    // menerapkan scope-nya sendiri.
                    ->whereNull("mdc_transactions.deleted_at");
            })
            ->select(
                "employees.employee_id",
                "employees.fullname",
                "employees.company_name",
                "mdc_transactions.usage_id",
                "mdc_transactions.created_at",
                "mdc_transactions.no_medic",
                "mdc_transactions.no_invoice",
                "mdc_transactions.hospital_name",
                "mdc_transactions.patient_name",
                "mdc_transactions.disease",
                "mdc_transactions.medical_type",
                "mdc_transactions.balance",
                "mdc_transactions.status",
            );

        // --- filter transaksi ---
        if (
            $request->filled("start_date") &&
            $request->filled("end_date")
        ) {
            $query->whereBetween("mdc_transactions.created_at", [
                $request->input("start_date"),
                Carbon::parse($request->input("end_date"))->addDay(),
            ]);
            $hasFilter = true;
        }

        if ($request->filled("statusMDC")) {
            $query->where(
                "mdc_transactions.status",
                $request->input("statusMDC"),
            );
            $hasFilter = true;
        }

        // --- filter karyawan ---
        if ($request->filled("stat")) {
            $query->where(
                "employees.group_company",
                $request->input("stat"),
            );
            $hasFilter = true;
        }

        if ($request->filled("unit")) {
            $query->where(
                "employees.work_area_code",
                $request->input("unit"),
            );
            $hasFilter = true;
        }

        if ($request->filled("customsearch")) {
            $query->where(
                "employees.fullname",
                "LIKE",
                "%" . $request->input("customsearch") . "%",
            );
            $hasFilter = true;
        }

        if (!empty($restrictedWorkAreas)) {
            $query->whereIn(
                "employees.work_area_code",
                $restrictedWorkAreas,
            );
        }

        return [$query, $hasFilter];
    }

    /**
     * Sumber data DataTables server-side untuk tabel "Medical Data Employee".
     */
    public function medicalReportAdminData(Request $request)
    {
        [$query, $hasFilter] = $this->medicalReportJoinedQuery($request);

        $draw = (int) $request->input("draw", 0);
        $masterMedical = MasterMedical::where("active", "T")->get();

        // Tanpa filter, dan ketika Unit Location di-set '-', versi lama tidak
        // menampilkan baris apa pun. Perilaku itu dipertahankan.
        if (!$hasFilter || $request->input("stat") === "-") {
            return response()->json([
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ]);
        }

        $recordsTotal = (clone $query)->count();

        $search = trim((string) $request->input("search.value", ""));
        if ($search !== "") {
            $query->where(function ($q) use ($search) {
                foreach (
                    [
                        "employees.fullname",
                        "employees.company_name",
                        "mdc_transactions.no_medic",
                        "mdc_transactions.no_invoice",
                        "mdc_transactions.hospital_name",
                        "mdc_transactions.patient_name",
                        "mdc_transactions.disease",
                        "mdc_transactions.medical_type",
                        "mdc_transactions.status",
                    ]
                    as $column
                ) {
                    $q->orWhere($column, "like", "%" . $search . "%");
                }
            });
        }

        $recordsFiltered =
            $search === "" ? $recordsTotal : (clone $query)->count();

        // Kolom 0 adalah nomor urut, 10..13 nilai per jenis medical, 15 aksi:
        // tidak bisa diurutkan di SQL.
        $sortable = [
            1 => "mdc_transactions.created_at",
            2 => "mdc_transactions.no_medic",
            3 => "mdc_transactions.no_invoice",
            4 => "mdc_transactions.hospital_name",
            5 => "employees.company_name",
            6 => "employees.fullname",
            7 => "mdc_transactions.patient_name",
            8 => "mdc_transactions.disease",
            9 => "mdc_transactions.medical_type",
        ];
        $statusIdx = 10 + $masterMedical->count();
        $orderColumn = (int) $request->input("order.0.column", -1);
        $orderDir =
            strtolower($request->input("order.0.dir", "asc")) === "desc"
                ? "desc"
                : "asc";

        if ($orderColumn === $statusIdx) {
            $query->orderBy("mdc_transactions.status", $orderDir);
        } elseif (isset($sortable[$orderColumn])) {
            $query->orderBy($sortable[$orderColumn], $orderDir);
        } else {
            // Urutan bawaan versi lama: karyawan terbaru dulu.
            $query->orderBy("employees.created_at", "desc");
        }

        // Tiebreaker wajib: tanpa ini urutan baris dengan created_at sama
        // tidak stabil antar halaman, sehingga baris bisa terlewat atau
        // muncul dua kali saat paging.
        $query
            ->orderBy("employees.employee_id")
            ->orderBy("mdc_transactions.usage_id");

        $start = max(0, (int) $request->input("start", 0));
        $length = (int) $request->input("length", 50);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $rows = $query->get();

        $data = [];
        foreach ($rows as $i => $row) {
            $data[] = $this->medicalReportRowCells(
                $row,
                $start + $i,
                $masterMedical,
            );
        }

        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        ]);
    }

    /**
     * Satu baris tabel "Medical Data Employee" sebagai array sel DataTables.
     *
     * $rowIndex adalah indeks global, jadi nomor urutnya berlanjut antar
     * halaman (bukan mengulang dari 1 seperti bug $loop->iteration dulu).
     */
    private function medicalReportRowCells(
        $row,
        int $rowIndex,
        $masterMedical
    ): array {
        $cells = [
            (string) ($rowIndex + 1),
            e($row->created_at),
            e($row->no_medic),
            e($row->no_invoice),
            e($row->hospital_name),
            e($row->company_name),
            e($row->fullname),
            e($row->patient_name),
            e($row->disease),
            e($row->medical_type),
        ];

        foreach ($masterMedical as $type) {
            $cells[] =
                $row->medical_type == $type->name
                    ? "Rp. " . number_format((float) $row->balance, 0, ",", ".")
                    : "Rp. 0";
        }

        $cells[] = e($row->status);

        $noMedic = e($row->no_medic);
        $cells[] =
            '<form id="deleteForm_' .
            $noMedic .
            '" method="POST" action="' .
            e(route("medicalReport-admin.delete", $row->usage_id)) .
            '" style="display: inline-block;">' .
            csrf_field() .
            method_field("DELETE") .
            '<input type="hidden" id="no_sppd_' .
            $noMedic .
            '" value="' .
            $noMedic .
            '">' .
            '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill delete-button" data-id="' .
            $noMedic .
            '"><i class="bi bi-trash-fill"></i></button></form>';

        return $cells;
    }

    public function medicalAdminDetail(Request $request, $key)
    {
        // Gunakan findByRouteKey untuk mendekripsi $key
        // dd($key);
        $employee_id = decrypt($key);

        // Ambil data dependents, medical, dan medical_plan berdasarkan employee_id
        $family = Dependents::orderBy("date_of_birth", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical = HealthCoverage::orderBy("created_at", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical_plan = HealthPlan::orderBy("period", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medicalGroup = HealthCoverage::select(
            "no_medic",
            "date",
            "period",
            "hospital_name",
            "patient_name",
            "disease",
            "verif_by",
            "balance_verif",
            "approved_by",
            "medical_proof",
            DB::raw(
                'SUM(CASE WHEN medical_type = "Maternity" THEN balance ELSE 0 END) as maternity_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Inpatient" THEN balance ELSE 0 END) as inpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Outpatient" THEN balance ELSE 0 END) as outpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Glasses" THEN balance ELSE 0 END) as glasses_total',
            ),
            "status",
            DB::raw("MAX(created_at) as latest_created_at"),
        )
            ->where("employee_id", $employee_id)
            ->where("status", "!=", "Draft")
            ->groupBy(
                "no_medic",
                "date",
                "period",
                "hospital_name",
                "patient_name",
                "disease",
                "status",
                "verif_by",
                "balance_verif",
                "approved_by",
                "medical_proof",
            )
            ->orderBy("latest_created_at", "desc")
            ->get();

        $medicalGroup->map(function ($item) {
            $approvedEmployee = Employee::where(
                "employee_id",
                $item->approved_by,
            )->first();

            $item->approved_by_fullname = $approvedEmployee
                ? $approvedEmployee->fullname
                : null;
            $item->total_per_no_medic =
                $item->maternity_total +
                $item->inpatient_total +
                $item->outpatient_total +
                $item->glasses_total;
            return $item;
        });

        $bissnisUnit = Employee::where("employee_id", $employee_id)->pluck(
            "group_company",
        );
        $gaApproval = MasterBusinessUnit::where(
            "nama_bisnis",
            $bissnisUnit,
        )->first();
        $gaFullname = Employee::where(
            "employee_id",
            $gaApproval->approval_medical,
        )->pluck("fullname");

        $rejectMedic = HealthCoverage::where("employee_id", $employee_id)
            ->where("status", "Rejected") // Filter for rejected status
            ->get()
            ->keyBy("no_medic");

        // Get employee IDs from both 'employee_id' and 'rejected_by'
        $employeeIds = $rejectMedic
            ->pluck("employee_id")
            ->merge($rejectMedic->pluck("rejected_by"))
            ->unique();

        // Fetch employee names for those IDs
        $employees = Employee::whereIn("employee_id", $employeeIds)->pluck(
            "fullname",
            "employee_id",
        );

        // Now map the full names to the respective HealthCoverage records
        $rejectMedic->transform(function ($item) use ($employees) {
            $item->employee_fullname = $employees->get($item->employee_id);
            $item->rejected_by_fullname = $employees->get($item->rejected_by);
            return $item;
        });

        // dd($rejectMedic);
        $medical = $medicalGroup->map(function ($item) use ($employee_id) {
            // Fetch the usage_id based on no_medic
            $usageId = HealthCoverage::where("no_medic", $item->no_medic)
                ->where("employee_id", $employee_id)
                ->value("usage_id"); // Assuming there's one usage_id per no_medic

            // Add usage_id to the current item
            $item->usage_id = $usageId;

            return $item;
        });

        $master_medical = MasterMedical::where("active", "T")->get();

        // Format data medical_plan
        $formatted_data = [];
        foreach ($medical_plan as $plan) {
            $formatted_data[$plan->period][$plan->medical_type] =
                $plan->balance;
        }

        $employees_cast = Employee::where("employee_id", $employee_id)->get();
        $currentYear = date("Y");

        foreach ($employees_cast as $employee) {
            $startDate = $employee->date_of_joining;
            $job_level = $employee->job_level;
            $endDate = date("Y-12-31");

            $startDate = date_create($startDate);
            $endDate = date_create($endDate);
            $difference = date_diff($startDate, $endDate);
            $yearsWorked = $difference->y;

            $plafond_list = MasterPlafond::where(
                "group_name",
                $job_level,
            )->get();

            if ($yearsWorked > 0) {
                foreach ($plafond_list as $plafond_lists) {
                    $existingHealthPlan = HealthPlan::where(
                        "employee_id",
                        $employee->employee_id,
                    )
                        ->where("period", $currentYear)
                        ->where("medical_type", $plafond_lists->medical_type)
                        ->first();

                    if (!$existingHealthPlan) {
                        $newHealthPlan = HealthPlan::create([
                            "plan_id" => (string) Str::uuid(),
                            "employee_id" => $employee->employee_id,
                            "medical_type" => $plafond_lists->medical_type,
                            "balance" => $plafond_lists->balance,
                            "period" => $currentYear,
                            "created_by" => $employee_id,
                            "created_at" => now(),
                        ]);

                        if ($newHealthPlan) {
                            session()->flash("refresh", true);
                        }
                    }
                }
            } elseif ($yearsWorked == 0) {
                $startDate = date_create($employee->date_of_joining);

                $tanggal = date_format($startDate, "d");
                if ($tanggal > 15) {
                    $bulan_awal = date_format($startDate, "m") + 1; // Bulan setelahnya
                    // Jika bulan menjadi 13 (Desember ke Januari), reset ke 01
                    if ($bulan_awal == 13) {
                        $bulan_awal = "1";
                    }
                } else {
                    $bulan_awal = date_format($startDate, "m"); // Bulan tetap
                }

                $bulan_akhir = 12;
                $bulan = $bulan_akhir - $bulan_awal + 1;

                foreach ($plafond_list as $plafond_lists) {
                    $balance = 0;

                    $existingHealthPlan = HealthPlan::where(
                        "employee_id",
                        $employee->employee_id,
                    )
                        ->where("period", $currentYear)
                        ->where("medical_type", $plafond_lists->medical_type)
                        ->first();

                    if (!$existingHealthPlan) {
                        if ($plafond_lists->medical_type == "Maternity") {
                            // $balance = $plafond_lists->balance * ($bulan / 12);
                            $balance = $plafond_lists->balance;
                        } elseif ($plafond_lists->medical_type == "Inpatient") {
                            $balance = $plafond_lists->balance * ($bulan / 12);
                        } elseif (
                            $plafond_lists->medical_type == "Outpatient"
                        ) {
                            $balance = $plafond_lists->balance * ($bulan / 12);
                        } elseif ($plafond_lists->medical_type == "Glasses") {
                            // $balance = $plafond_lists->balance * ($bulan / 12);
                            $balance = $plafond_lists->balance;
                        }

                        $newHealthPlan = HealthPlan::create([
                            "plan_id" => (string) Str::uuid(),
                            "employee_id" => $employee->employee_id,
                            "medical_type" => $plafond_lists->medical_type,
                            "balance" => $balance,
                            "period" => $currentYear,
                            "created_by" => $employee_id,
                            "created_at" => now(),
                        ]);

                        if ($newHealthPlan) {
                            session()->flash("refresh", true);
                        }
                    }
                }
            }
        }

        $year = date("Y");
        $month = date("m");
        $ym = $year . "-" . $month;
        $today = date("Y-m-d");

        $count = Dependents::where("employee_id", $employee_id)
            ->whereDate("updated_at", $today)
            ->count();

        if ($count > 0) {
            // return response()->json(['message' => 'Data sudah diperbarui bulan ini.']);
        } else {
            Log::info("updateDependents method started.");

            $url =
                "https://kpncorporation.darwinbox.com/orgmasterapi/getDependentDetails";

            $data = [
                "api_key" =>
                    "ae8304c9205210085def58cc613e25725872b55a6502af24eaac1586ff5dad673ed44092bd63f938433fe84839d56951f8dc110256c2b9732d55af04d01eef41",
                "employee_number" => [$employee_id],
            ];

            $headers = [
                "Content-Type" => "application/json",
                "Authorization" =>
                    "Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh",
            ];

            try {
                Log::info("Sending request to API", [
                    "url" => $url,
                    "data" => $data,
                ]);

                $response = Http::withHeaders($headers)->post($url, $data);

                if ($response->successful()) {
                    $apiResponse = $response->json();
                    Log::info("Decoded API Response", [
                        "response" => $apiResponse,
                    ]);

                    if (
                        $apiResponse["status"] == 1 &&
                        !empty($apiResponse["data"])
                    ) {
                        $data = $apiResponse["data"];

                        foreach ($data as $dependent) {
                            Dependents::updateOrInsert(
                                ["array_id" => $dependent[2]],
                                [
                                    "id" => Str::uuid(),
                                    "employee_id" => $dependent[0],
                                    "name" => trim(
                                        $dependent[3] .
                                            "" .
                                            $dependent[4] .
                                            " " .
                                            $dependent[5],
                                    ),
                                    "array_id" => $dependent[2],
                                    "first_name" => $dependent[3],
                                    "middle_name" => $dependent[4],
                                    "last_name" => $dependent[5],
                                    "relation_type" =>
                                        $dependent[6] == "Son"
                                            ? "Child"
                                            : $dependent[6],
                                    "contact_details" => $dependent[7],
                                    "phone" => $dependent[8],
                                    "date_of_birth" => Carbon::createFromFormat(
                                        "d-m-Y",
                                        $dependent[9],
                                    )->format("Y-m-d"),
                                    "nationality" => $dependent[14],
                                    "updated_on" => Carbon::createFromFormat(
                                        "d-m-Y",
                                        $dependent[15],
                                    )->format("Y-m-d"),
                                    "jobs" => $dependent[16],
                                    "gender" => explode("/", $dependent[17])[0],
                                    "no_bpjs" => $dependent[18],
                                    "education" => $dependent[19],
                                    "updated_at" => now(),
                                ],
                            );
                        }
                        // return response()->json(['message' => 'Dependents data updated successfully.']);
                    } else {
                        // return response()->json(['error' => 'No data found in API response.'], 404);
                    }
                    // return response()->json(['message' => 'Dependents data updated successfully.']);
                } else {
                    // return response()->json(['error' => 'Failed API.'], 500);
                }
            } catch (\Exception $e) {
                Log::error("Exception occurred in updateDependents method", [
                    "error" => $e->getMessage(),
                ]);
                // return response()->json(['message' => 'An error occurred: '.$e->getMessage()], 500);
            }
        }

        $family = Dependents::orderBy("date_of_birth", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $parentLink = "Reimbursement";
        $sublink = "Medical";
        $link = "Detail";

        // Kirim data ke view
        return view(
            "hcis.reimbursements.medical.admin.medicalAdmin",
            compact(
                "family",
                "medical_plan",
                "medical",
                "parentLink",
                "sublink",
                "link",
                "rejectMedic",
                "employees",
                "employee_id",
                "master_medical",
                "formatted_data",
                "medicalGroup",
                "gaFullname",
            ),
        );
    }

    public function medicalAdminEditPlafon(Request $request, $period, $employee)
    {
        $employee_id = $employee;
        $period = $period;

        $medicalData = $request->except(["_token"]);

        // Loop melalui data medical untuk menyimpan atau memperbarui
        foreach ($medicalData as $medicalType => $balance) {
            HealthPlan::updateOrCreate(
                [
                    "employee_id" => $employee_id,
                    "period" => $period,
                    "medical_type" => $medicalType,
                ],
                [
                    "balance" => str_replace(".", "", $balance), // Hapus format angka sebelum disimpan
                ],
            );
        }

        // Kirim data ke view
        return redirect()->back()->with("success", "Plafon are edited");
    }

    public function medicalAdminDetailForm($key)
    {
        // Gunakan findByRouteKey untuk mendekripsi $key
        $employee_id = decrypt($key);

        // $employee_id = Auth::user()->employee_id;
        $currentYear = now()->year;
        $families = Dependents::orderBy("date_of_birth", "desc")
            ->where("employee_id", $employee_id)
            ->get();
        $medical_type = MasterMedical::orderBy("id", "desc")
            ->where("active", "T")
            ->get();

        $medicalBalances = HealthPlan::where("employee_id", $employee_id)
            // ->where('period', $currentYear)
            ->get();
        $balanceData = [];
        foreach ($medicalBalances as $balance) {
            // Assuming `medical_type` is a property of `HealthPlan`
            $balanceData[$balance->medical_type][$balance->period] =
                $balance->balance;
        }

        $employee_name = Employee::select("fullname")
            ->where("employee_id", $employee_id)
            ->first();

        $diseases = MasterDisease::orderBy("id", "asc")
            ->where("active", "T")
            ->get();
        $parentLink = "Medical";
        $link = "Add Medical Coverage Usage";

        return view(
            "hcis.reimbursements.medical.admin.form.medicalFormAdmin",
            compact(
                "diseases",
                "medical_type",
                "families",
                "parentLink",
                "link",
                "employee_name",
                "balanceData",
                "employee_id",
            ),
        );
    }

    public function medicalAdminDetailCreate(Request $request, $key)
    {
        $userId = Auth::id();
        $employee_id = decrypt($key);
        $no_medic = $this->generateNoMedic();

        $statusValue = $request->has("action_draft") ? "Draft" : "Pending";

        $docStatusValue = "Claim Verified";

        if ($statusValue == "Draft") {
            $docStatusValue = "Draft";
        }

        $contribution_level_code = Employee::where("employee_id", $employee_id)
            ->pluck("contribution_level_code")
            ->first();

        // Handle medical proof file upload
        $employee_data = Employee::where("id", $userId)->first();

        if ($request->has("removed_medical_proof")) {
            $removedFiles = json_decode($request->removed_medical_proof, true);
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];

            // Hapus file yang ada di server
            foreach ($removedFiles as $fileToRemove) {
                if (in_array($fileToRemove, $existingFiles)) {
                    $filePath = public_path($fileToRemove);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $existingFiles = array_filter(
                        $existingFiles,
                        fn($file) => $file !== $fileToRemove,
                    );
                }
            }
        } else {
            $existingFiles = $request->existing_medical_proof
                ? json_decode($request->existing_medical_proof, true)
                : [];
        }

        // Proses file baru
        if ($request->hasFile("medical_proof")) {
            $request->validate([
                "medical_proof.*" =>
                    "required|mimes:jpeg,png,jpg,gif,pdf|max:2048",
            ]);

            foreach ($request->file("medical_proof") as $file) {
                $filename = time() . "_" . $file->getClientOriginalName();
                $upload_path = "uploads/proofs/" . $employee_data->employee_id;
                $file->storeAs($upload_path, $filename, "public");
                $existingFiles[] = $upload_path . "/" . $filename;
            }
        }

        // Simpan semua file yang tersisa ke database
        $medical_proof_path = json_encode(array_values($existingFiles));

        $medical_costs = $request->input("medical_costs", []);
        $date = Carbon::parse($request->date);
        $period = $date->year;

        foreach ($medical_costs as $medical_type => $cost) {
            $cost = (int) str_replace(".", "", $cost);

            $medical_plan = HealthPlan::where("employee_id", $employee_id)
                ->where("period", $period)
                ->where("medical_type", $medical_type)
                ->first();

            if (!$medical_plan) {
                continue;
            }

            HealthCoverage::create([
                "usage_id" => (string) Str::uuid(),
                "employee_id" => $employee_id,
                "contribution_level_code" => $contribution_level_code,
                "no_medic" => $no_medic,
                "no_invoice" => $request->no_invoice,
                "hospital_name" => $request->hospital_name,
                "patient_name" => $request->patient_name,
                "disease" => $request->disease,
                "date" => $date,
                "coverage_detail" => $request->coverage_detail,
                "period" => $period,
                "medical_type" => $medical_type,
                "balance" => $cost,
                "balance_verif" => $cost,
                "balance_uncoverage" => 0,
                "created_by" => Auth::user()->employee_id,
                "verif_by" => Auth::user()->employee_id,
                "status" => $statusValue,
                "doc_status" => $docStatusValue,
                "doc_status_previous" => $docStatusValue,
                "doc_received_by" => Auth::user()->employee_id,
                "doc_received_at" => Carbon::now(),
                "verif_at" => Carbon::now(),
                "medical_proof" => $medical_proof_path,
            ]);
        }

        return redirect()
            ->route("medical.detail", ["key" => encrypt($employee_id)])
            ->with("success", "Medical successfully added.");
    }

    // ===================================================================
    // LEGACY - diganti oleh medicalAdminConfirmation() di bawah.
    // Disimpan utuh sebagai pembanding.
    // ===================================================================
//    public function medicalAdminConfirmation(Request $request)
//    {
//        // Ambil data dependents, medical, dan medical_plan berdasarkan employee_id
//        $family = Dependents::orderBy("date_of_birth", "desc")->get();
//
//        $permissionLocations = $this->permissionLocations;
//        $permissionCompanies = $this->permissionCompanies;
//        $permissionGroupCompanies = $this->permissionGroupCompanies;
//
//        $userRole = auth()->user()->roles->first();
//        $roleRestriction = json_decode($userRole->restriction, true);
//        // dd($roleRestriction);
//
//        $restrictedWorkAreas = $roleRestriction["work_area_code"] ?? [];
//        $restrictedGroupCompanies = $roleRestriction["group_company"] ?? [];
//
//        $locations = !empty($restrictedWorkAreas)
//            ? Location::orderBy("area")
//                ->whereIn("work_area", $restrictedWorkAreas)
//                ->get()
//            : Location::orderBy("area")->get();
//
//        $hasFilter = false;
//
//        $query = HealthCoverage::from(
//            "mdc_transactions as mdc_transactions",
//        )->join(
//            "employees",
//            "mdc_transactions.employee_id",
//            "=",
//            "employees.employee_id",
//        );
//
//        // Ambil data medical plan
//        $medical_plan = HealthPlan::orderBy("period", "desc")->get();
//
//        // Filter berdasarkan request input 'stat'
//        if ($request->has("stat") && $request->input("stat") !== null) {
//            $status = $request->input("stat");
//
//            if ($status !== "-") {
//                $query->where("employees.work_area_code", $status);
//                $hasFilter = true;
//            }
//        }
//
//        // Jika tidak ada filter dari request, gunakan default permission lokasi
//        if (!$hasFilter && !empty($restrictedWorkAreas)) {
//            $query->whereIn("employees.work_area_code", $restrictedWorkAreas);
//        }
//
//        // Tambahkan filter permission lain
//        if (!empty($permissionLocations)) {
//            $query->whereIn("employees.work_area_code", $permissionLocations);
//        }
//        if (!empty($permissionCompanies)) {
//            $query->whereIn(
//                "employees.contribution_level_code",
//                $permissionCompanies,
//            );
//        }
//        if (!empty($permissionGroupCompanies)) {
//            $query->whereIn(
//                "employees.group_company",
//                $permissionGroupCompanies,
//            );
//        }
//
//        // Select dan agregasi
//        $medicalGroup = $query
//            ->select(
//                "mdc_transactions.no_medic",
//                "mdc_transactions.date",
//                "mdc_transactions.employee_id",
//                "mdc_transactions.period",
//                "mdc_transactions.hospital_name",
//                "mdc_transactions.patient_name",
//                "mdc_transactions.disease",
//                DB::raw(
//                    'SUM(CASE WHEN mdc_transactions.medical_type = "Maternity" THEN mdc_transactions.balance ELSE 0 END) as maternity_total',
//                ),
//                DB::raw(
//                    'SUM(CASE WHEN mdc_transactions.medical_type = "Inpatient" THEN mdc_transactions.balance ELSE 0 END) as inpatient_total',
//                ),
//                DB::raw(
//                    'SUM(CASE WHEN mdc_transactions.medical_type = "Outpatient" THEN mdc_transactions.balance ELSE 0 END) as outpatient_total',
//                ),
//                DB::raw(
//                    'SUM(CASE WHEN mdc_transactions.medical_type = "Glasses" THEN mdc_transactions.balance ELSE 0 END) as glasses_total',
//                ),
//                "mdc_transactions.status",
//                "mdc_transactions.doc_status",
//                "mdc_transactions.is_revise",
//                DB::raw(
//                    "MAX(mdc_transactions.created_at) as latest_created_at",
//                ),
//            )
//            ->whereNotIn("mdc_transactions.status", [
//                "Draft",
//                "Done",
//                "Rejected",
//            ])
//            ->whereNull("mdc_transactions.verif_by")
//            ->whereNull("mdc_transactions.balance_verif")
//            ->groupBy(
//                "mdc_transactions.no_medic",
//                "mdc_transactions.date",
//                "mdc_transactions.period",
//                "mdc_transactions.hospital_name",
//                "mdc_transactions.patient_name",
//                "mdc_transactions.disease",
//                "mdc_transactions.status",
//                "mdc_transactions.doc_status",
//                "mdc_transactions.is_revise",
//                "mdc_transactions.employee_id",
//            )
//            ->orderBy("latest_created_at", "desc")
//            ->get();
//
//        // Proses data untuk ditampilkan di view
//        $medical = $medicalGroup->map(function ($item) {
//            $usageId = HealthCoverage::where(
//                "no_medic",
//                $item->no_medic,
//            )->value("usage_id");
//            $item->usage_id = $usageId;
//            return $item;
//        });
//
//        $rejectMedic = HealthCoverage::where("status", "Rejected") // Filter for rejected status
//            ->get()
//            ->keyBy("no_medic");
//
//        // Get employee IDs from both 'employee_id' and 'rejected_by'
//        $employeeIds = $rejectMedic
//            ->pluck("employee_id")
//            ->merge($rejectMedic->pluck("rejected_by"))
//            ->unique();
//        $status = $request->input("stat");
//        $employees = Employee::whereIn("employee_id", $employeeIds)
//            ->where("work_area_code", $status)
//            ->pluck("fullname", "employee_id");
//
//        // Now map the full names to the respective HealthCoverage records
//        $rejectMedic->transform(function ($item) use ($employees) {
//            $item->employee_fullname = $employees->get($item->employee_id);
//            $item->rejected_by_fullname = $employees->get($item->rejected_by);
//            return $item;
//        });
//
//        $medical = $medical->map(function ($item) {
//            $item->total_per_no_medic =
//                $item->maternity_total +
//                $item->inpatient_total +
//                $item->outpatient_total +
//                $item->glasses_total;
//            $item->employee_fullname = $item->employee->fullname ?? "N/A";
//            return $item;
//        });
//
//        $master_medical = MasterMedical::where("active", "T")->get();
//
//        // Format data medical_plan
//        $formatted_data = [];
//        foreach ($medical_plan as $plan) {
//            $formatted_data[$plan->period][$plan->medical_type] =
//                $plan->balance;
//        }
//
//        $parentLink = "Reimbursement";
//        $sublink = "Medical";
//        $link = "Confirmation";
//
//        // Kirim data ke view
//        return view(
//            "hcis.reimbursements.medical.admin.medicalAdmin",
//            compact(
//                "family",
//                "medical_plan",
//                "medical",
//                "parentLink",
//                "sublink",
//                "link",
//                "rejectMedic",
//                "employees",
//                "master_medical",
//                "formatted_data",
//                "locations",
//                "hasFilter",
//                "medicalGroup",
//            ),
//        );
//    }

    public function medicalAdminConfirmation(Request $request)
    {
        // $family dan $medical_plan TIDAK dipakai di route ini.
        // medicalAdmin.blade.php hanya merender tab Family Data dan Plafon
        // Medical di dalam @if (request()->routeIs('medical.detail')), jadi
        // familyDataAdmin / plafonMedicalAdmin tidak pernah di-render di
        // /medical/confirmation. Versi lama tetap memuat 7.946 baris
        // dependents + 78.224 baris mdc_plans lalu membuangnya.
        $family = collect();
        $medical_plan = collect();
        $formatted_data = [];

        $restrictedWorkAreas =
            json_decode(auth()->user()->roles->first()->restriction, true)[
                "work_area_code"
            ] ?? [];

        $locations = !empty($restrictedWorkAreas)
            ? Location::orderBy("area")
                ->whereIn("work_area", $restrictedWorkAreas)
                ->get()
            : Location::orderBy("area")->get();

        $hasFilter =
            $request->input("stat") !== null &&
            $request->input("stat") !== "-";

        // Baris tabel diambil per halaman lewat medicalAdminConfirmationData()
        // (DataTables server-side), jadi halaman ini tidak lagi merender 3.183
        // baris sekaligus. Keduanya dikirim kosong supaya compact() di bawah
        // tidak berubah bentuknya.
        $medical = collect();
        $medicalGroup = collect();

        // $rejectMedic hanya dipakai showRejectInfo(), yang hanya terpasang
        // lewat onclick pada baris ber-status 'Rejected'. Query halaman ini
        // selalu whereNotIn status ['Draft','Done','Rejected'], jadi baris
        // seperti itu tidak pernah ada di sini dan fungsinya tidak pernah
        // dipanggil. Versi lama tetap men-json_encode 243 model penuh ke
        // dalam halaman (~264 KB).
        // CATATAN: kalau suatu saat route ini ikut menampilkan status
        // 'Rejected', isi lagi kedua variabel ini.
        $rejectMedic = collect();
        $employees = collect();

        $master_medical = MasterMedical::where("active", "T")->get();

        $parentLink = "Reimbursement";
        $sublink = "Medical";
        $link = "Confirmation";

        // Kirim data ke view
        return view(
            "hcis.reimbursements.medical.admin.medicalAdmin",
            compact(
                "family",
                "medical_plan",
                "medical",
                "parentLink",
                "sublink",
                "link",
                "rejectMedic",
                "employees",
                "master_medical",
                "formatted_data",
                "locations",
                "hasFilter",
                "medicalGroup",
            ),
        );
    }

    /**
     * Query dasar daftar konfirmasi medical.
     *
     * Dipakai bersama oleh halaman dan endpoint DataTables-nya supaya filter
     * permission tidak pernah berbeda antara keduanya. Mengembalikan builder
     * yang BELUM dieksekusi.
     */
    private function confirmationGroupedQuery(Request $request)
    {
        $permissionLocations = $this->permissionLocations;
        $permissionCompanies = $this->permissionCompanies;
        $permissionGroupCompanies = $this->permissionGroupCompanies;

        $restrictedWorkAreas =
            json_decode(auth()->user()->roles->first()->restriction, true)[
                "work_area_code"
            ] ?? [];

        $hasFilter = false;

        $query = HealthCoverage::from(
            "mdc_transactions as mdc_transactions",
        )->join(
            "employees",
            "mdc_transactions.employee_id",
            "=",
            "employees.employee_id",
        );

        if ($request->has("stat") && $request->input("stat") !== null) {
            $stat = $request->input("stat");

            if ($stat !== "-") {
                $query->where("employees.work_area_code", $stat);
                $hasFilter = true;
            }
        }

        if (!$hasFilter && !empty($restrictedWorkAreas)) {
            $query->whereIn("employees.work_area_code", $restrictedWorkAreas);
        }

        if (!empty($permissionLocations)) {
            $query->whereIn("employees.work_area_code", $permissionLocations);
        }
        if (!empty($permissionCompanies)) {
            $query->whereIn(
                "employees.contribution_level_code",
                $permissionCompanies,
            );
        }
        if (!empty($permissionGroupCompanies)) {
            $query->whereIn(
                "employees.group_company",
                $permissionGroupCompanies,
            );
        }

        return $query
            ->select(
                "mdc_transactions.no_medic",
                "mdc_transactions.date",
                "mdc_transactions.employee_id",
                "mdc_transactions.period",
                "mdc_transactions.hospital_name",
                "mdc_transactions.patient_name",
                "mdc_transactions.disease",
                DB::raw(
                    'SUM(CASE WHEN mdc_transactions.medical_type = "Maternity" THEN mdc_transactions.balance ELSE 0 END) as maternity_total',
                ),
                DB::raw(
                    'SUM(CASE WHEN mdc_transactions.medical_type = "Inpatient" THEN mdc_transactions.balance ELSE 0 END) as inpatient_total',
                ),
                DB::raw(
                    'SUM(CASE WHEN mdc_transactions.medical_type = "Outpatient" THEN mdc_transactions.balance ELSE 0 END) as outpatient_total',
                ),
                DB::raw(
                    'SUM(CASE WHEN mdc_transactions.medical_type = "Glasses" THEN mdc_transactions.balance ELSE 0 END) as glasses_total',
                ),
                "mdc_transactions.status",
                "mdc_transactions.doc_status",
                "mdc_transactions.is_revise",
                DB::raw("MAX(employees.fullname) as employee_fullname"),
                DB::raw(
                    "MAX(mdc_transactions.created_at) as latest_created_at",
                ),
            )
            ->whereNotIn("mdc_transactions.status", [
                "Draft",
                "Done",
                "Rejected",
            ])
            ->whereNull("mdc_transactions.verif_by")
            ->whereNull("mdc_transactions.balance_verif")
            ->groupBy(
                "mdc_transactions.no_medic",
                "mdc_transactions.date",
                "mdc_transactions.period",
                "mdc_transactions.hospital_name",
                "mdc_transactions.patient_name",
                "mdc_transactions.disease",
                "mdc_transactions.status",
                "mdc_transactions.doc_status",
                "mdc_transactions.is_revise",
                "mdc_transactions.employee_id",
            );
    }

    /**
     * Sumber data DataTables server-side untuk /medical/confirmation.
     *
     * Halaman hanya mengirim satu halaman baris (default 5) alih-alih seluruh
     * 3.183 baris, sehingga HTML turun dari ~9,3 MB.
     */
    /**
     * Query dasar daftar /medical/admin/table.
     *
     * Dipakai bersama oleh halaman dan endpoint DataTables-nya.
     */
    private function adminTableGroupedQuery()
    {
        return HealthCoverage::select(
            "no_medic",
            "date",
            "period",
            "hospital_name",
            "patient_name",
            "disease",
            DB::raw(
                'SUM(CASE WHEN medical_type = "Maternity" THEN balance ELSE 0 END) as maternity_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Inpatient" THEN balance ELSE 0 END) as inpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Outpatient" THEN balance ELSE 0 END) as outpatient_total',
            ),
            DB::raw(
                'SUM(CASE WHEN medical_type = "Glasses" THEN balance ELSE 0 END) as glasses_total',
            ),
            "status",
            "doc_status",
            "is_revise",
            DB::raw("MAX(created_at) as latest_created_at"),
        )
            ->where("status", "!=", "Draft")
            ->whereNull("verif_by")
            ->whereNull("balance_verif")
            ->groupBy(
                "no_medic",
                "date",
                "period",
                "hospital_name",
                "patient_name",
                "disease",
                "status",
                "doc_status",
                "is_revise",
            );
    }

    /**
     * Tata letak kolom tabel medical.
     *
     * Kedua route memakai historyMedicalAdmin.blade.php yang sama, tetapi
     * route konfirmasi punya satu kolom ekstra (Employee Name), sehingga
     * indeks kolom sesudahnya bergeser satu. Mengembalikan
     * [peta kolom yang bisa diurutkan, indeks kolom master_medical pertama].
     */
    private function medicalTableColumns(bool $isConfirmation): array
    {
        $ordered = $isConfirmation
            ? [
                "employee_fullname",
                "latest_created_at",
                "date",
                "period",
                "no_medic",
                "hospital_name",
                "patient_name",
                "__total",
                "disease",
            ]
            : [
                "latest_created_at",
                "date",
                "period",
                "no_medic",
                "hospital_name",
                "patient_name",
                "__total",
                "disease",
            ];

        $sortable = [];
        foreach ($ordered as $i => $name) {
            // 0 = kolom control, 1 = nomor urut
            $sortable[$i + 2] = $name;
        }

        // Setelah kolom di atas ada kolom Attachment, lalu master_medical.
        $masterFirst = count($ordered) + 3;

        return [$sortable, $masterFirst];
    }

    /**
     * Sumber data DataTables server-side untuk /medical/confirmation.
     */
    public function medicalAdminConfirmationData(Request $request)
    {
        return $this->medicalTableDataResponse(
            $request,
            $this->confirmationGroupedQuery($request),
            true,
            collect(),
        );
    }

    /**
     * Sumber data DataTables server-side untuk /medical/admin/table.
     *
     * Berbeda dengan route konfirmasi, query di sini tidak mengecuali status
     * 'Rejected', jadi $rejectMedic tetap diperlukan untuk tooltip/onclick
     * showRejectInfo().
     */
    public function medicalAdminTableData(Request $request)
    {
        $rejectMedic = HealthCoverage::where("status", "Rejected")
            ->get()
            ->keyBy("no_medic");

        return $this->medicalTableDataResponse(
            $request,
            $this->adminTableGroupedQuery(),
            false,
            $rejectMedic,
        );
    }

    /**
     * Pembentuk respons DataTables server-side untuk kedua route di atas.
     */
    private function medicalTableDataResponse(
        Request $request,
        $grouped,
        bool $isConfirmation,
        $rejectMedic
    ) {
        // Dibungkus subquery karena query utamanya memakai GROUP BY:
        // count() langsung akan menghitung per grup, bukan jumlah grup.
        $base = DB::query()->fromSub($grouped, "g");

        $recordsTotal = (clone $base)->count();

        $search = trim((string) $request->input("search.value", ""));
        $filtered = clone $base;

        if ($search !== "") {
            $searchable = [
                "no_medic",
                "hospital_name",
                "patient_name",
                "disease",
                "period",
                "status",
            ];

            if ($isConfirmation) {
                $searchable[] = "employee_fullname";
            }

            $filtered->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, "like", "%" . $search . "%");
                }
            });
        }

        // Tanpa kata kunci, hasilnya pasti sama dengan $recordsTotal. Hitung
        // ulang hanya kalau memang ada pencarian: setiap count() menjalankan
        // GROUP BY penuh atas ~43rb baris.
        $recordsFiltered =
            $search === "" ? $recordsTotal : (clone $filtered)->count();

        [$sortable, $masterFirst] = $this->medicalTableColumns($isConfirmation);
        $masterMedical = MasterMedical::where("active", "T")->get();
        $statusIdx = $masterFirst + $masterMedical->count();

        $orderColumn = (int) $request->input("order.0.column", -1);
        $orderDir =
            strtolower($request->input("order.0.dir", "desc")) === "asc"
                ? "asc"
                : "desc";

        // Urutan default sama dengan versi lama: latest_created_at desc.
        if ($orderColumn === $statusIdx) {
            $filtered->orderBy("status", $orderDir);
        } elseif (($sortable[$orderColumn] ?? null) === "__total") {
            $filtered->orderByRaw(
                "(maternity_total + inpatient_total + outpatient_total + glasses_total) " .
                    $orderDir,
            );
        } elseif (isset($sortable[$orderColumn])) {
            $filtered->orderBy($sortable[$orderColumn], $orderDir);
        } else {
            $filtered->orderBy("latest_created_at", "desc");
        }

        $start = max(0, (int) $request->input("start", 0));
        $length = (int) $request->input("length", 5);

        if ($length > 0) {
            $filtered->offset($start)->limit($length);
        }

        $rows = $filtered->get();

        // usage_id hanya untuk baris di halaman ini.
        $usageIds = HealthCoverage::whereIn(
            "no_medic",
            $rows->pluck("no_medic"),
        )
            ->selectRaw("no_medic, MIN(usage_id) as usage_id")
            ->groupBy("no_medic")
            ->pluck("usage_id", "no_medic");

        $isProperty =
            auth()->check() &&
            auth()->user()->employee &&
            strtolower(auth()->user()->employee->group_company) == "property";

        $data = [];
        foreach ($rows as $i => $row) {
            $row->usage_id = $usageIds[$row->no_medic] ?? null;
            $data[] = $this->medicalTableRowCells(
                $row,
                $start + $i,
                $masterMedical,
                $isProperty,
                $isConfirmation,
                $rejectMedic,
            );
        }

        return response()->json([
            "draw" => (int) $request->input("draw", 0),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        ]);
    }

    /**
     * Satu baris tabel sebagai array sel DataTables.
     *
     * Markup-nya mengikuti historyMedicalAdmin.blade.php; $rowIndex adalah
     * indeks global (bukan indeks halaman) supaya kondisi tombol hapus
     * ($loop->first || status Pending) tetap sama dengan versi lama.
     */
    private function medicalTableRowCells(
        $item,
        int $rowIndex,
        $masterMedical,
        bool $isProperty,
        bool $isConfirmation,
        $rejectMedic
    ): array {
        $rupiah = fn($v) => "Rp. " . number_format((float) $v, 0, ",", ".");

        $total =
            $item->maternity_total +
            $item->inpatient_total +
            $item->outpatient_total +
            $item->glasses_total;

        $cells = ["", (string) ($rowIndex + 1)];

        // Kolom Employee Name hanya ada di route konfirmasi.
        if ($isConfirmation) {
            $cells[] = e($item->employee_fullname ?? "N/A");
        }

        $cells[] = \Carbon\Carbon::parse($item->latest_created_at)->format(
            "d M Y",
        );
        $cells[] = \Carbon\Carbon::parse($item->date)->format("d M Y");
        $cells[] = e($item->period);
        $cells[] = e($item->no_medic);
        $cells[] = e($item->hospital_name);
        $cells[] = e($item->patient_name);
        $cells[] = $rupiah($total);
        $cells[] = e($item->disease);

        // Attachment: medical_proof tidak ada di SELECT yang di-group, sama
        // seperti versi lama, sehingga tombolnya memang tidak pernah muncul.
        $cells[] = !empty($item->medical_proof)
            ? '<button type="button" class="btn btn-sm btn-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#viewAttachmentModal-' .
                e($item->no_medic) .
                '">View</button>'
            : "";

        foreach ($masterMedical as $type) {
            $field =
                strtolower(str_replace(" ", "_", $type->name)) . "_total";
            $cells[] = $rupiah($item->$field ?? 0);
        }

        $cells[] = $isProperty
            ? $this->medicalPropertyStatusBadge($item)
            : $this->medicalStatusBadge($item, $rejectMedic);

        $cells[] = $this->medicalActionButtons(
            $item,
            $rowIndex,
            $isProperty,
            $isConfirmation,
        );

        return $cells;
    }

    private function medicalPropertyStatusBadge($item): string
    {
        $status = !empty($item->doc_status) ? $item->doc_status : $item->status;

        $badgeClass = match ($status) {
            "Pending", "Document Pending", "Document Received",
            "Claim Verified", "Revise"
                => "bg-warning",
            "Reimbursement Approved", "Processing Payment", "Done", "Paid"
                => "bg-success",
            "Rejected" => "bg-danger",
            "Draft" => "bg-secondary",
            default => "bg-light",
        };

        if ($item->is_revise) {
            $status .= " (Revise)";
        }

        return '<span class="badge rounded-pill ' .
            $badgeClass .
            ' text-center" style="font-size: 12px; padding: 0.5rem 1rem; cursor: pointer;">' .
            e($status) .
            "</span>";
    }

    private function medicalStatusBadge($item, $rejectMedic): string
    {
        $badgeClass = match ($item->status) {
            "Pending" => "bg-warning",
            "Done" => "bg-success",
            "Rejected" => "bg-danger",
            "Draft" => "bg-secondary",
            default => "bg-light",
        };

        if (
            $item->status == "Rejected" &&
            isset($rejectMedic[$item->no_medic])
        ) {
            $attrs =
                ' onclick="showRejectInfo(&#039;' .
                e($item->no_medic) .
                '&#039;)" title="Click to see rejection reason"';
        } elseif ($item->status == "Done") {
            $attrs =
                ' title="' .
                e("Approved GA by - " . ($item->approved_by_fullname ?? "") . " ") .
                '"';
        } else {
            // Kedua query selalu memfilter verif_by & balance_verif NULL,
            // jadi cabang ternary di view selalu yang pertama.
            $attrs = ' title="Waiting for Admin GA to confirmation"';
        }

        return '<span class="badge rounded-pill ' .
            $badgeClass .
            ' text-center" style="font-size: 12px; padding: 0.5rem 1rem; cursor: pointer;"' .
            $attrs .
            ">" .
            e($item->status) .
            "</span>";
    }

    private function medicalActionButtons(
        $item,
        int $rowIndex,
        bool $isProperty,
        bool $isConfirmation
    ): string {
        $noMedic = e($item->no_medic);
        $usageId = e($item->usage_id);
        $html = "";

        // Tombol edit hanya ada di route konfirmasi.
        if ($isConfirmation) {
            $html .=
                '<form method="GET" action="/medical/admin/form-update/' .
                $usageId .
                '" style="display: inline-block;">' .
                '<button type="submit" class="btn btn-outline-warning rounded-pill my-1" data-toggle="tooltip" title="Edit">' .
                '<i class="bi bi-pencil-square"></i></button></form>';
        }

        if ($rowIndex === 0 || $item->status == "Pending") {
            $html .=
                '<form id="deleteForm_' .
                $noMedic .
                '" method="POST" action="/medical/admin/delete/' .
                $usageId .
                '" style="display: inline-block;">' .
                csrf_field() .
                method_field("DELETE") .
                '<input type="hidden" id="no_sppd_' .
                $noMedic .
                '" value="' .
                $noMedic .
                '">' .
                '<button type="button" class="btn btn-outline-danger rounded-pill delete-button" data-id="' .
                $noMedic .
                '"><i class="bi bi-trash-fill"></i></button></form>';
        }

        if ($isProperty) {
            $html .=
                '<a href="' .
                e(route("medical.download", $item->usage_id)) .
                '" target="_blank" class="btn btn-outline-info" title="Print">' .
                '<i class="bi bi-file-earmark-arrow-down"></i></a>';
        }

        return $html;
    }

    public function importAdminExcel(Request $request)
    {
        $userId = Auth::id();

        // Validate the uploaded file
        $request->validate([
            "file" => "required|mimes:xlsx,xls",
            "attachment" => "nullable|mimes:pdf|max:3072", // Optional, max 3MB
        ]);

        // // Create instance of import class
        // $import = new ImportHealthCoverage();

        // // Import the data
        // Excel::import($import, $request->file('file'));

        // // After import is complete, process the batched records and send emails
        // $import->afterImport();

        // return redirect()->route('medical.admin')->with('success', 'Transaction successfully added from Excel.');

        try {
            $attachmentPath = null;
            if ($request->hasFile("imp_attachment")) {
                $attachment = $request->file("imp_attachment");
                $attachmentName =
                    "proof_" .
                    time() .
                    "_" .
                    $attachment->getClientOriginalName();
                $uploadPath = "uploads/proofs/import_medical";

                // Save file to storage
                $attachmentPath = $attachment->storeAs(
                    $uploadPath,
                    $attachmentName,
                    "public",
                );
                $existingFiles[] = $uploadPath . "/" . $attachmentName;
            }

            // Create instance of import class
            $import = new ImportHealthCoverage($attachmentPath);

            // Import the data
            Excel::import($import, $request->file("file"));

            // Ambil data yang gagal
            $failedRows = $import->afterImport();

            // After import is complete, process the batched records and send emails
            $import->afterImport();

            if (!empty($failedRows)) {
                // Simpan file gagal ke session (sementara)
                $filePath = "failed_imports/failed_import_" . time() . ".xlsx";
                Excel::store(
                    new \App\Exports\MedicalFailedImportExport($failedRows),
                    $filePath,
                    "public",
                );

                // Simpan path file ke session
                session()->put(
                    "failed_import_path",
                    asset("storage/" . $filePath),
                );

                return redirect()
                    ->back()
                    ->with(
                        "failed",
                        "Transaction successfully added from Excel, but some of them failed.",
                    );
            } else {
                return redirect()
                    ->back()
                    ->with(
                        "success",
                        "Transaction successfully added from Excel.",
                    );
            }
        } catch (\App\Exceptions\ImportDataInvalidException $e) {
            // Catch custom exception and redirect back with error message
            return redirect()
                ->back()
                ->withErrors(["import_error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Catch any other unexpected exceptions and redirect with a generic error message
            return redirect()
                ->back()
                ->withErrors([
                    "import_error" =>
                        "An error occurred during import. Please check the file format.",
                ]);
        }
    }

    public function exportAdminExcel(Request $request)
    {
        $statusMDC = $request->input("statusMDC");
        $stat = $request->input("stat");
        $customSearch = $request->input("customsearch");
        $startDate = $request->input("start_date");
        $endDate = $request->input("end_date");
        $unit = $request->input("unit");

        return Excel::download(
            new MedicalExport(
                $statusMDC,
                $stat,
                $customSearch,
                $startDate,
                $endDate,
                $unit,
            ),
            "medical_report.xlsx",
        );
    }

    public function exportAdminPlafondExcel(Request $request)
    {
        $statusMDC = $request->input("statusMDC");
        $stat = $request->input("stat");
        $customSearch = $request->input("customsearch");
        $startDate = $request->input("start_date");
        $endDate = $request->input("end_date");
        $unit = $request->input("unit");

        return Excel::download(
            new MedicalPlafondExport(
                $statusMDC,
                $stat,
                $customSearch,
                $startDate,
                $endDate,
                $unit,
            ),
            "medical_plafond_report.xlsx",
        );
    }

    public function exportDetailExcel($employee_id)
    {
        return Excel::download(
            new MedicalDetailExport($employee_id),
            "medical_detail.xlsx",
        );
    }
}
