<?php

namespace App\DTO;

class ODataMedicalClaimResponseResultItemDTO extends BaseDTO {
    public function __construct(
        public readonly string $company_code,
        public readonly string $nik,
        public readonly string $cust_code,
        public readonly string $paid_date,
        public readonly string $hcis_id,
        public readonly string $curr,
        public readonly int $amount,
    ) {}

    public static function fromArray(array $response): static {
        return new static(
            company_code: $response['Company_code'] ?? '',
            nik: $response['NIK'] ?? '',
            cust_code: $response['cust_code'] ?? '',
            paid_date: $response['Paid_date'] ?? '',
            hcis_id: $response['Hcis_Id'] ?? '',
            curr: $response['Curr'] ?? '',
            amount: static::toInt($response['Amount'] ?? null),
        );
    }
}
