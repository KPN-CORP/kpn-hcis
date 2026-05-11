<?php

namespace App\Services;

use App\Models\HealthCoverage as HealthCoverageModel;
use App\Models\Employee as EmployeeModel;
use App\Models\ELogInsertFirstReceiptRequestDTO;
use App\Models\ELogInsertFirstReceiptResponseDTO;

class ELogService {
    public static function insertFirstReceipt(HealthCoverageModel $medicalData, EmployeeModel $employeeData) {
        $payload = new ELogInsertFirstReceiptRequestDTO(
            extsyscompanycode => $medicalData->contribution_level_code ?? "",
            invoice_code => $medicalData->no_invoice ?? "",
            no_po => $medicalData->no_medic ?? "",
            vendor => $medicalData->employee_id ?? "",
            amount => $medicalData->balance ?? 0,
            notes => $medicalData->coverage_detail ?? "",
            first_dept => "IT",
            created_by => "ERPKPN",
            inv_date => $medicalData->date ?? "",
            trans_type => "MEDICAL",
        );

        $httpClient = app(HttpClient::class);

        $httpRes = $httpClient->postJSON("/api/log-firstreceipt", $payload, [
            "Authorization": "Bearer 123"
        ]);
        if (!$httpRes["status"]) {
            return [
                'status' => false,
                'message'  => "failed",
                'data'    => null,
                'error'   => $httpRes["error"],
            ];
        }

        $resData = ELogInsertFirstReceiptResponseDTO::fromArray($httpRes["data"]);
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
}
