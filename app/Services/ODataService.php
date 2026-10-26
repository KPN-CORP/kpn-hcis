<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use App\Models\Employee as EmployeeModel;
use App\Models\HealthCoverage as HealthCoverageModel;
use App\DTO\ODataLoginRequestDTO;
use App\DTO\ODataLoginResponseDTO;
use App\DTO\ODataMedicalClaimRequestDTO;
use App\DTO\ODataMedicalClaimResponseDTO;

class ODataService {
    protected string $apiBaseUrl;
    protected string $apiUsername;
    protected string $apiPassword;
    protected string $apiClientID;
    protected string $apiClientSecret;
    protected string $apiAccessTokenKey;

    public function __construct() {
        $this->apiBaseUrl = config('services.odata.api_base_url');
        $this->apiUsername = config('services.odata.api_username');
        $this->apiPassword = config('services.odata.api_password');
        $this->apiClientID = config('services.odata.api_client_id');
        $this->apiClientSecret = config('services.odata.api_client_secret');
        $this->apiAccessTokenKey = config('services.odata.api_access_token_key');
    }

    public function login() {
        $payload = new ODataLoginRequestDTO(
            clientId: $this->apiClientID,
            clientSecret: $this->apiClientSecret,
        );

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON($this->apiBaseUrl . "/api/v1/auth", $payload, []);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ODataLoginResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => null,
            ];
        }

        $ttl = now()->addMinutes(55);

        $expiresIn = $resData->expires_in ?? null;

        if (is_numeric($expiresIn) && (int) $expiresIn > 0) {
            $ttl = now()->addSeconds(max(30, (int) $expiresIn - 60));
        }

        if ($resData && $resData->access_token) {
            Cache::put($this->apiAccessTokenKey, $resData->access_token, $ttl);
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
            'error'   => null,
        ];
    }

    public function syncMedicalPaidDate($id) {
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
        if ($medicalData->paid_date && $medicalData->paid_date != null && !empty($medical->paid_date)) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "Medical paid date data already sync with the OData",
            ];
        }

        $employeeID = $medicalData->employee_id ?? "";

        $employeeData = EmployeeModel::where("employee_id", $employeeID)->first();
        if (!$employeeData) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "Employee data not found",
            ];
        }
        if ($employeeData->employee_id != $employeeID) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => "Employee data doesn't match",
            ];
        }

        $payload = new ODataMedicalClaimRequestDTO(
            employee_id: $employeeID
        );

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->getJSON($this->apiBaseUrl . "/sap/opu/odata/sap/ZCDS_MED_CLAIM_CDS/ZCDS_MED_CLAIM?" . $payload->toUrlEncode(), [
            "Authorization" => "Basic " . base64_encode($this->apiUsername . ":" . $this->apiPassword),
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        // $accessToken = $this->getAccessToken();

        // $httpClient = app(HttpClient::class);

        // $httpRes = $httpClient->getJSON($this->apiBaseUrl . "/api/v1/medical-claim?" . $payload->toUrlEncode(), [
        //     "Authorization" => $accessToken,
        // ]);
        // if (!$httpRes["status"]) {
        //     return [
        //         'status' => false,
        //         'message'  => "failed",
        //         'data'    => null,
        //         'error'   => $httpRes["error"],
        //     ];
        // }

        $resData = ODataMedicalClaimResponseDTO::fromArray($httpRes["data"] ?? []);
        if (!$resData || !$resData->d || empty($resData->d) || !$resData->d->results || empty($resData->d->results)) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => $resData,
                'error'   => "No data to be processed",
            ];
        }

        foreach ($resData->d->results as $row) {
            if (!$row || !$row->hcis_id || !$row->nik || !$row->paid_date) {
                continue;
            }

            if ($row->hcis_id != $medicalData->no_medic || $row->nik != $employeeID) {
                continue;
            }

            $paidDate = Carbon::createFromFormat('Ymd', $row->paid_date)
                ->startOfDay()
                ->toDateTimeString();

            $medicalData->paid_date = $paidDate;
            $medicalData->doc_status_previous = $medicalData->doc_status;
            $medicalData->doc_status = "Done";

            $medicalData->save();
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
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

        $tokenType = $loginRes["data"]->token_type ?? "Bearer ";

        return $tokenType . $loginRes["data"]->access_token;
    }
}
