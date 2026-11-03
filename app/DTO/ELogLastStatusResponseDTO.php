<?php

namespace App\DTO;

class ELogLastStatusResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly int $count,
        public readonly array $data,
    ) {}

    public static function fromArray(array $response): static {
        return new static(
            status: $response['status'] ?? '',
            count: (int) ($response['count'] ?? 0),
            data: $response['data'] ?? [],
        );
    }
}
