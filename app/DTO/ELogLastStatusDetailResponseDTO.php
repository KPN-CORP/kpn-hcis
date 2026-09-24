<?php

namespace App\DTO;

class ELogLastStatusDetailResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly int $count,
        public readonly array $data,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            status: $response['status'] ?? '',
            count: (int) ($response['count'] ?? 0),
            data: $response['data'] ?? [],
        );
    }
}
