<?php

namespace App\DTO;

class ODataMedicalClaimResponseDataDTO extends BaseDTO {
    public function __construct(
        public readonly string $company_code,
        public readonly string $nik,
        public readonly string $paid_date,
        public readonly string $hcis_id,
        public readonly string $currency,
        public readonly string $amount,
    ) {}

    public static function fromArray(array $response): static {
        return new static(
            company_code: $response['companyCode'] ?? '',
            nik: $response['NIK'] ?? '',
            paid_date: $response['paidDate'] ?? '',
            hcis_id: $response['hcisId'] ?? '',
            currency: $response['currency'] ?? '',
            amount: $response['amount'] ?? '',
        );
    }
}
