<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use App\Models\Employee as EmployeeModel;
use App\Models\HealthPlan as HealthPlanModel;
use App\Models\HealthCoverage as HealthCoverageModel;
use App\Models\MasterSAPBankName as MasterSAPBankNameModel;
use App\DTO\ELogInsertFirstReceiptRequestDTO;
use App\DTO\ELogInsertFirstReceiptResponseDTO;
use App\DTO\ELogLoginRequestDTO;
use App\DTO\ELogLoginResponseDTO;
use App\DTO\ELogHistoryResponseDTO;
use App\DTO\ELogLastStatusResponseDTO;
use App\DTO\ELogLastStatusDetailResponseDTO;

class ELogService {
    protected string $apiBaseUrl;
    protected string $apiLoginUsername;
    protected string $apiLoginPassword;
    protected string $apiAccessTokenKey;

    public function __construct() {
        $this->apiBaseUrl = config('services.elog.api_base_url');
        $this->apiLoginUsername = config('services.elog.api_login_username');
        $this->apiLoginPassword = config('services.elog.api_login_password');
        $this->apiAccessTokenKey = config('services.elog.api_access_token_key');
    }

    public function login() {
        $payload = new ELogLoginRequestDTO(
            username: $this->apiLoginUsername,
            password: $this->apiLoginPassword,
        );

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON($this->apiBaseUrl . "/login", $payload, []);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogLoginResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData || $resData->status != "success") {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        if ($resData && $resData->token) {
            Cache::put($this->apiAccessTokenKey, $resData->token, now()->addMinutes(55));
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
            'error'   => null,
        ];
    }

    public function insertFirstReceipt($id) {
        $today = Carbon::now();

        $medicalData = HealthCoverageModel::where("usage_id", $id)
            ->where("status", "Done")
            ->whereNull("deleted_at")
            ->first();
        if (!$medicalData) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "No medical data",
            ];
        }
        if ($medicalData->elog_insert_first_receipt_sync_date && $medicalData->elog_insert_first_receipt_sync_date != null && !empty($medical->elog_insert_first_receipt_sync_date)) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "Medical data already sync with the ELog",
            ];
        }

        $noMedic = $medicalData->no_medic ?? "";

        $medicalDataList = HealthCoverageModel::where("no_medic", $noMedic)
            ->where("status", "Done")
            ->whereNull("deleted_at")
            ->get();
        if ($medicalDataList->isEmpty()) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "No medical data list",
            ];
        }

        $period = $today->year;
        $bankName = "";
        $namaPemilikRekening = "";
        $costCenterCode = "";
        $employeeID = $medicalData->employee_id;
        $createdName = "";
        $plafondType = "";
        $notes = "";
        $invoiceCode = "";
        $extsyscompanycode = "";
        $invDate = "";
        $createdBy = "";
        $vendor = "SMEDICAL";
        $elogMedicalType = "Reimbursement";
        $noRekening = "";
        $firstDept = "HRD";
        $medicalTypes = [];
        $isCompanyCoveredAmount = false;
        $isUsePlafondAmountBefore = false;
        $usageAmountTemp = 0;
        $amount = 0;
        $nonReimbursableAmount = 0;
        $companyCoveredAmount = 0;
        $medicalPlanBalance = 0;
        $sisaOverPlafond = 0;
        $coveredAmount = 0;

        foreach ($medicalDataList as $row) {
            $usageAmount = $row->balance_verif ?? $row->balance ?? 0;

            if ($row->elog_medical_type === "Reimbursement" && $row->company_covered_amount !== null) {
                $usageAmount = $row->company_covered_amount;
            }

            $nonReimbursableAmount += $row->employee_covered_amount ?? $row->balance_uncoverage ?? 0;
            $amount += $usageAmount;
            $companyCoveredAmount += $row->company_covered_amount ?? 0;
            $medicalPlanBalance += $row->plafond_amount_before ?? 0;

            if ($row->company_covered_amount !== null) {
                $isCompanyCoveredAmount = true;
            }

            if ($row->plafond_amount_before !== null) {
                $isUsePlafondAmountBefore = true;
            }

            if ($row->medical_type && !empty($row->medical_type)) {
                $medicalTypes[] = $row->medical_type;
            }

            if ($usageAmount > $usageAmountTemp) {
                $period = $row->period ?? $today->year;
                $usageAmountTemp = $usageAmount;
                $plafondType = $row->medical_type ?? "";
                $notes = $row->coverage_detail ?? "";
                $invoiceCode = $row->no_invoice ?? "";
                $extsyscompanycode = $row->contribution_level_code ?? "";
                $invDate = $row->date ?? "";
                $createdBy = $row->approved_by ?? $row->verif_by ?? "";
                $vendor = $row->elog_vendor ?? "SMEDICAL";
                $elogMedicalType = $row->elog_medical_type ?? "Reimbursement";
            }
        }

        $employeeData = EmployeeModel::where("employee_id", $employeeID)->first();
        if ($employeeData) {
            $employeeID = $employeeData->employee_id ?? $employeeID;
            $bankName = $employeeData->bank_name ?? $bankName;
            $namaPemilikRekening = $employeeData->bank_account_name_payroll ?? $employeeData->bank_account_name ?? $namaPemilikRekening;
            $costCenterCode = $employeeData->cost_center_code ?? $costCenterCode;
            $createdName = $employeeData->fullname ?? $createdName;
            $noRekening = $employeeData->bank_account_number ?? $noRekening;

            if (strtolower($employeeData->group_company) == "downstream") {
                $firstDept = "HRD-DWS";
            } else if (strtolower($employeeData->group_company) == "kpn corporation") {
                $firstDept = "HRD-CORP";
            }
        }

        $medicalPlanList = HealthPlanModel::where("employee_id", $employeeID)
            ->where("period", $period)
            ->whereIn("medical_type", $medicalTypes)
            ->whereNull("deleted_at")
            ->get();
        if ($medicalPlanList->isEmpty()) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "No medical plan list",
            ];
        }

        if (!$isUsePlafondAmountBefore) {
            foreach ($medicalPlanList as $row) {
                $medicalPlanBalance += $row->balance ?? 0;
            }
        }

        if ($isCompanyCoveredAmount) {
            $coveredAmount = $companyCoveredAmount;
        } else if ($medicalPlanBalance <= 0) {
            $coveredAmount = 0;
        } else if ($medicalPlanBalance > 0) {
            if ($amount > $medicalPlanBalance) {
                $coveredAmount = $medicalPlanBalance;
            } else if ($amount < $medicalPlanBalance) {
                $coveredAmount = $amount;
            } else if ($amount == $medicalPlanBalance) {
                $coveredAmount = $medicalPlanBalance;
            }
        }

        $sisaOverPlafond = -max(0, $amount - $coveredAmount - $nonReimbursableAmount);

        $masterSAPBankName = MasterSAPBankNameModel::where("hcis_bank_name", $bankName)->first();
        if ($masterSAPBankName) {
            $bankName = $masterSAPBankName->sap_bank_name ?? $bankName ?? "";
        }

        $payload = new ELogInsertFirstReceiptRequestDTO(
            extsyscompanycode: $extsyscompanycode,
            invoice_code: $invoiceCode,
            no_po: $noMedic,
            vendor: $vendor,
            amount: $amount,
            sisa_over_plafond: $sisaOverPlafond,
            non_reimbursable_amount: $nonReimbursableAmount * -1,
            nik: $employeeID,
            no_rekening: $noRekening,
            nama_pemilik_rekening: $namaPemilikRekening,
            nama_bank: $bankName,
            cost_center: $costCenterCode,
            medical_type: $elogMedicalType,
            plafond_type: $plafondType,
            notes: $notes,
            first_dept: $firstDept,
            created_name: $createdName,
            created_by: $createdBy,
            inv_date: $invDate,
            trans_type: "MEDICAL",
            currency: "IDR"
        );

        $accessToken = $this->getAccessToken();

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON($this->apiBaseUrl . "/log-firstreceipt", $payload->toUpperCaseArray(), [
            "Authorization" => "Bearer " . $accessToken
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogInsertFirstReceiptResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData || $resData->status != "success") {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        foreach ($medicalDataList as $row) {
            $row->elog_insert_first_receipt_sync_date = $today;
            $row->doc_status_previous = $row->doc_status;
            $row->doc_status = "Processing Payment";

            $row->save();
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
            'error'   => null,
        ];
    }

    public function getLastStatuses() {
        $accessToken = $this->getAccessToken();

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->getJSON($this->apiBaseUrl . "/last-status", $payload, [
            "Authorization" => "Bearer " . $accessToken
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogLastStatusResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData || $resData->status != "success") {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData->data,
            'error'   => null,
        ];
    }

    public function getLastStatus(string $noReceiptDoc) {
        $accessToken = $this->getAccessToken();

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->getJSON($this->apiBaseUrl . "/last-status?no_receipt_doc=" . $noReceiptDoc, $payload, [
            "Authorization" => "Bearer " . $accessToken
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogLastStatusDetailResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData || $resData->status != "success") {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData->data,
            'error'   => null,
        ];
    }

    public function getHistory(string $noReceiptDoc) {
        $accessToken = $this->getAccessToken();

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->getJSON($this->apiBaseUrl . "/history?no_receipt_doc=" . $noReceiptDoc, $payload, [
            "Authorization" => "Bearer " . $accessToken
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogHistoryResponseDTO::fromArray($httpRes["data"]);
        if (!$resData || $resData->status != "success") {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData->data,
            'error'   => null,
        ];
    }

    private function getAccessToken() {
        $token = Cache::get($this->apiAccessTokenKey);
        if ($token && !empty($token)) {
            return $token;
        }

        $loginRes = $this->login();
        if (!$loginRes || !$loginRes["status"] || !$loginRes["data"]) {
            return "";
        }

        return $loginRes["data"]->token;
    }
}
