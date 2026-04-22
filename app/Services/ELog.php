<?php

namespace App\Services;

use App\Models\HealthCoverage as HealthCoverageModel;
use App\Models\Employee as EmployeeModel;
use App\Models\ELogInsertFirstReceiptRequestDTO;
use App\Models\ELogInsertFirstReceiptResponseDTO;

class ELogService {
    public static function insertFirstReceipt(HealthCoverageModel $medicalData, EmployeeModel $employeeData) {
        $payload = new ELogInsertFirstReceiptRequestDTO(
            noMedic: "",
        );

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON("", $payload, [
            "Authorization": "Bearer 123",
        ]);

        $resData = ELogInsertFirstReceiptResponseDTO::fromArray($httpRes);
    }
}
