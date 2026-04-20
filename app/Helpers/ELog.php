<?php

namespace App\Helpers;

use Illuminate\Support\Str;

use App\Helpers\Padding as PaddingHelper;
use App\Models\ELogCompanyExtSys as ELogCompanyExtSysModel;
use App\Models\ELogFirstReceipt as ELogFirstReceiptModel;
use App\Models\ELogSupplier as ELogSupplierModel;
use App\Models\ELogUsersTab as ELogUsersTabModel;
use App\Models\HealthCoverage as HealthCoverageModel;
use App\Models\Department as DepartmentModel;

class ELog {
    public static function generateInsertData(HealthCoverageModel $medicalData, DepartmentModel $department) {
        $company = self::generateCompany($medicalData);
        $data = [
            'UUID' => self::generateUUID(),
            'COMPANY' => $company,
            'NO_RECEIPT_DOC' => self::generateNoReceiptDoc($company),
            'INVOICE_CODE' => self::generateInvoiceCode($medicalData),
            'NO_PO' => self::generateNoPO($medicalData),
            'VENDOR' => self::generateVendor($medicalData),
            'CURRENCY' => self::generateCurrency($medicalData),
            'AMOUNT' => self::generateAmount($medicalData),
            'FIRST_DEPT' => self::generateFirstDept($department),
            'CREATED_BY' => self::generateCreatedBy($medicalData),
            'CREATED_AT' => self::generateCreatedAt($medicalData),
            'NOTES' => self::generateNotes($medicalData),
            'TOTAL_AMOUNT' => self::generateTotalAmount($medicalData),
            'TRANS_TYPE' => self::generateTransType(),
            'STATUS_PRIORITY' => self::generateStatusPriority(),
            'AMOUNT_PPN' => self::generateAmountPPN(),
            'PROFORMA' => self::generateProforma(),
            'AMT_SUB_TYPE' => self::generateAmtSubType(),
            'STATUS_PKP' => self::generateStatusPKP(),
            'VAT_CONVERSION' => self::generateVATConversion(),
            'RATE_CURRENCY' => self::generateRateCurrency(),
            'INV_DATE' => self::generateInvDate($medicalData),
            'RETENSI' => self::generateRetensi(),
            'RETENSI_PERCENTAGE' => self::generateRetensiPercentage(),
            'RETENSI_TOTAL' => self::generateRetensiTotal(),
            'OTHER_CHARGE' => self::generateOtherCharge(),
            'TOTALX' => self::generateTotalX($medicalData),
            'SOURCE' => self::generateSource(),
            'IS_RETENSI' => self::generateIsRetensi(),
            'POTONGAN_DP' => self::generatePotonganDP(),
            'DPP_NILAI_LAIN' => self::generateDPPNilaiLain(),
            'PPN_FAKTUR' => self::generatePPNFaktur(),
            'DPP_PPH' => self::generateDPPPPH(),
            'DP_PERCENTAGE' => self::generateDPPPercentage()
        ];

        return $data;
    }

    public static function generateUUID() {
        return Str::uuid()->toString();
    }

    public static function generateNoReceiptDoc($eLogCompany) {
        $sep = "-";
        $noReceiptDoc = "EH" . $sep . $eLogCompany . $sep;
        $timeNow = now();

        $noReceiptDoc += $timeNow->format('Y-m-d') . $sep;

        $docCount = PaddingHelper::leftZero(1, 4);

        $lastFirstReceipt = ELogFirstReceiptModel::where("NO_RECEIPT_DOC", "like", "%" . $noReceiptDoc . "%")
            ->orderBy('NO_RECEIPT_DOC', 'asc')
            ->first();
        if ($lastFirstReceipt && $lastFirstReceipt->NO_RECEIPT_DOC) {
            $docCount = PaddingHelper::incrementLeftZero(end(explode($sep, $lastFirstReceipt->NO_RECEIPT_DOC)), 4);
        }

        $noReceiptDoc += $docCount;

        return $noReceiptDoc;
    }

    public static function generateCompany(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->contribution_level_code) {
            return null
        }

        $hcisCompanyCode = $medicalData->contribution_level_code;

        $companyExtSys = ELogCompanyExtSysModel::with(["company"])
            ->where("EXTSYSTEM", "HCIS")
            ->where("EXTSYSCOMPANYCODE", $hcisCompanyCode)
            ->first();
        if (!$companyExtSys || !$companyExtSys->company || !$companyExtSys->company->ID) {
            return null;
        }

        return $companyExtSys->company->ID;
    }

    public static function generateInvoiceCode(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->no_invoice) {
            return null
        }

        return $medicalData->no_invoice;
    }

    public static function generateNoPO(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->no_medic) {
            return null
        }

        return $medicalData->no_medic;
    }

    public static function generateVendor(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->created_by) {
            return null
        }

        $hcisEmployeeID = $medicalData->created_by;

        $supplier = ELogSupplierModel::where("HCIS_USER", $hcisEmployeeID)
            ->first();
        if (!$supplier || !$supplier->ID) {
            return null;
        }

        return $supplier->ID;
    }

    public static function generateCurrency(HealthCoverageModel $medicalData) {
        return "IDR";
    }

    public static function generateAmount(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->balance) {
            return null
        }

        return $medicalData->balance;
    }

    public static function generateFirstDept(DepartmentModel $department) {
        if (!$department || !$department->parent_company_id) {
            return null
        }

        $firstDept = null;

        if (strtolower($department->parent_company_id) == "downstream") {
            $firstDept = "HRD-DWS";
        } else if (strtolower($department->parent_company_id) == "kpn corporation") {
            $firstDept = "HRD-CORP";
        } else { // TODO: THIS IS FOR UPSTREAM, PLEASE CONFIRM THIS
            $firstDept = "HRD";
        }

        return $firstDept;
    }

    public static function generateCreatedBy(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->verif_by) {
            return null
        }

        $eLogUser = ELogUsersTabModel::where("hcis_user", $medicalData->verif_by)
            ->first();
        if (!$eLogUser || !$eLogUser->FCCODE) {
            return null;
        }

        return $eLogUser->FCCODE;
    }

    public static function generateCreatedAt(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->verif_at) {
            return null
        }

        return $medicalData->verif_at->format('m/d/Y');
    }

    public static function generateNotes(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->coverage_detail) {
            return null
        }

        return $medicalData->coverage_detail;
    }

    public static function generateTotalAmount(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->balance) {
            return null
        }

        return $medicalData->balance;
    }

    public static function generateTransType() {
        return "CLAIM - MEDICAL REIMBURSEMENT";
    }

    public static function generateStatusPriority() {
        return "REGULAR";
    }

    public static function generateAmountPPN() {
        return 0;
    }

    public static function generateProforma() {
        return "NO";
    }

    public static function generateAmtSubType() {
        return "DPP";
    }

    public static function generateStatusPKP() {
        return "NON_PKP";
    }

    public static function generateVATConversion() {
        return 0;
    }

    public static function generateRateCurrency() {
        return 1;
    }

    public static function generateInvDate(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->date) {
            return null
        }

        return $medicalData->date->format('m/d/Y');
    }

    public static function generateRetensi() {
        return 0;
    }

    public static function generateRetensiPercentage() {
        return 0;
    }

    public static function generateRetensiTotal() {
        return 0;
    }

    public static function generateOtherCharge() {
        return 0;
    }

    public static function generateTotalX(HealthCoverageModel $medicalData) {
        if (!$medicalData || !$medicalData->balance) {
            return null
        }

        return $medicalData->balance;
    }

    public static function generateSource() {
        return "HCIS";
    }

    public static function generateIsRetensi() {
        return "NO";
    }

    public static function generatePotonganDP() {
        return 0;
    }

    public static function generateDPPNilaiLain() {
        return 0;
    }

    public static function generatePPNFaktur() {
        return 0;
    }

    public static function generateDPPPPH() {
        return 0;
    }

    public static function generateDPPPercentage() {
        return 0;
    }
}
