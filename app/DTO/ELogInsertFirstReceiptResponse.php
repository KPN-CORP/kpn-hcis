<?php

namespace App\DTO;

class ELogInsertFirstReceiptResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly bool $success,
        public readonly string $no_receipt_doc,
        public readonly string $company_id,
        public readonly string $vendor_id,
        public readonly string $data_message,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            status: $response['status'] ?? '',
            message: $response['message'] ?? '',
            success: $response['data']['success'] ?? false,
            no_receipt_doc: $response['data']['no_receipt_doc'] ?? '',
            company_id: $response['data']['company_id'] ?? '',
            vendor_id: $response['data']['vendor_id'] ?? '',
            data_message: $response['data']['message'] ?? '',
        );
    }
}
