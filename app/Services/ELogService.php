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
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogInsertFirstReceiptResponseDTO::fromArray($httpRes["data"]);

        return [
            'status' => true,
            'message'  => "",
            'data'    => $resData,
            'error'   => null,
        ];
    }
}
