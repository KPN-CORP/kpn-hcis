<?php

namespace App\Services;

use Carbon\Carbon;

use App\Models\Employee as EmployeeModel;
use App\Models\HealthCoverage as HealthCoverageModel;
use App\DTO\ODataMedicalClaimRequestDTO;
use App\DTO\ODataMedicalClaimResponseDTO;

class ODataService {
    protected string $apiBaseUrl;
    protected string $apiUsername;
    protected string $apiPassword;

    public function __construct() {
        $this->apiBaseUrl = config('services.odata.api_base_url');
        $this->apiUsername = config('services.odata.api_username');
        $this->apiPassword = config('services.odata.api_password');
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

            $medicalData->save();
        }

        return [
            'status' => true,
            'message'  => "success",
            'data'    => $resData,
            'error'   => null,
        ];
    }
}
