<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

use App\Models\HealthCoverage as HealthCoverageModel;
use App\Models\Employee as EmployeeModel;
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
            Cache::put($this->apiAccessTokenKey, $token, now()->addMinutes(55));
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
            'error'   => null,
        ];
    }

    public function insertFirstReceipt(HealthCoverageModel $medicalData, EmployeeModel|null $employeeData) {
        $payload = new ELogInsertFirstReceiptRequestDTO(
            extsyscompanycode: $medicalData->contribution_level_code ?? "",
            invoice_code: $medicalData->no_invoice ?? "",
            no_po: $medicalData->no_medic ?? "",
            vendor: $medicalData->employee_id ?? "",
            amount: $medicalData->balance ?? 0,
            notes: $medicalData->coverage_detail ?? "",
            first_dept: "",
            created_by: "",
            inv_date: $medicalData->date ?? "",
            trans_type: "MEDICAL",
        );

        if ($medicalData->approved_by) {
            $payload->created_by = $medicalData->approved_by;
        } else if ($medicalData->verif_by) {
            $payload->created_by = $medicalData->verif_by;
        }

        if ($employeeData) {
            if (strtolower($employeeData->group_company) == "downstream") {
                $payload->first_dept = "HRD-DWS";
            } else if (strtolower($employeeData->group_company) == "kpn corporation") {
                $payload->first_dept = "HRD-CORP";
            } else { // TODO: THIS IS FOR UPSTREAM, PLEASE CONFIRM THIS
                $payload->first_dept = "HRD";
            }
        }

        $accessToken = $this->getAccessToken();

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON($this->apiBaseUrl . "/log-firstreceipt", $payload, [
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
