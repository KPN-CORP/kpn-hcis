<?php

namespace App\DTO;

class ELogLastStatusResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly int $count,
        public readonly string $no_receipt_doc,
        public readonly string $company,
        public readonly int $amount,
        public readonly string $notes,
        public readonly string $last_action,
        public readonly int $pos,
        public readonly int $data_status,
        public readonly string $remark,
        public readonly string $last_updated_by,
        public readonly string $last_updated_at,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            status: $response['status'] ?? '',
            count: $response['count'] ?? '',
            no_receipt_doc: $response['data']['NO_RECEIPT_DOC'] ?? '',
            company: $response['data']['COMPANY'] ?? '',
            amount: $response['data']['AMOUNT'] ?? 0,
            notes: $response['data']['NOTES'] ?? '',
            last_action: $response['data']['LAST_ACTION'] ?? '',
            pos: $response['data']['POS'] ?? 0,
            data_status: $response['data']['STATUS'] ?? 0,
            remark: $response['data']['REMARK'] ?? '',
            last_updated_by: $response['data']['LAST_UPDATED_BY'] ?? '',
            last_updated_at: $response['data']['LAST_UPDATED_AT'] ?? '',
        );
    }
}
