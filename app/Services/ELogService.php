<?php

namespace App\Services;

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

    public function insertFirstReceipt(HealthCoverageModel $medicalData) {
        $bankName = "";
        $namaPemilikRekening = "";
        $costCenterCode = "";
        $employeeID = $medicalData->employee_id;

        $employeeData = EmployeeModel::where("employee_id", $employeeID)->first();
        if ($employeeData) {
            $employeeID = $employeeData->employee_id ?? $employeeID;
            $bankName = $employeeData->bank_name ?? $bankName;
            $namaPemilikRekening = $employeeData->bank_account_name_payroll ?? $employeeData->bank_account_name ?? $namaPemilikRekening;
            $costCenterCode = $employeeData->cost_center_code ?? $costCenterCode;
        }

        $medicalPlan = HealthPlanModel::where("employee_id", $employeeID)
            ->where("period", $medicalData->period)
            ->where("medical_type", $medicalData->medical_type)
            ->whereNull("deleted_at")
            ->first();

        $masterSAPBankName = MasterSAPBankNameModel::where("hcis_bank_name", $bankName)->first();
        if ($masterSAPBankName) {
            $bankName = $masterSAPBankName->sap_bank_name ?? $bankName;
        }

        $payload = new ELogInsertFirstReceiptRequestDTO(
            extsyscompanycode: $medicalData->contribution_level_code ?? "",
            invoice_code: $medicalData->no_invoice ?? "",
            no_po: $medicalData->no_medic ?? "",
            vendor: "SMEDICAL",
            amount: 0,
            sisa_over_plafond: 0,
            non_reimbursable_amount: $medicalData->balance_uncoverage ?? 0,
            nik: $employeeID,
            no_rekening: "",
            nama_pemilik_rekening: $namaPemilikRekening ?? "",
            nama_bank: $bankName ?? "",
            cost_center: $costCenterCode ?? "",
            medical_type: "Reimbursement",
            plafond_type: $medicalData->medical_type ?? "",
            notes: $medicalData->coverage_detail ?? "",
            first_dept: "",
            created_by: "",
            inv_date: $medicalData->date ?? "",
            trans_type: "MEDICAL",
        );

        if ($medicalData->balance_verif != null) {
            $payload->amount = $medicalData->balance_verif;
        } else {
            $payload->amount = $medicalData->balance ?? 0;
        }

        if ($medicalData->approved_by) {
            $payload->created_by = $medicalData->approved_by;
        } else if ($medicalData->verif_by) {
            $payload->created_by = $medicalData->verif_by;
        }

        if ($employeeData) {
            if ($employeeData->bank_account_number && !empty($employeeData->bank_account_number)) {
                $payload->no_rekening = $employeeData->bank_account_number;
            }

            if (strtolower($employeeData->group_company) == "downstream") {
                $payload->first_dept = "HRD-DWS";
            } else if (strtolower($employeeData->group_company) == "kpn corporation") {
                $payload->first_dept = "HRD-CORP";
                $payload->first_dept = "HRD";
            }
        } else {
            $payload->first_dept = "HRD";
        }

        if ($medicalPlan) {
            $payload->sisa_over_plafond = $medicalPlan->balance;
        }

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
        if ($token) {
            return $token;
        }

        $loginRes = $this->login();
        if (!$loginRes || !$loginRes["status"] || !$loginRes["data"]) {
            return "";
        }

        return $loginRes["data"]->token;
    }
}
