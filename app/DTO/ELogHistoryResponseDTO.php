<?php

namespace App\DTO;

class ELogHistoryResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly string $no_receipt_doc,
        public readonly int $count,
        public readonly array $data,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            status: $response['status'] ?? '',
            no_receipt_doc: $response['no_receipt_doc'] ?? '',
            count: (int) ($response['count'] ?? 0),
            data: $response['data'] ?? [],
        );
    }
}
