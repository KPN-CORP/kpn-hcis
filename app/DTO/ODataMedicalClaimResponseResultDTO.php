<?php

namespace App\DTO;

class ODataMedicalClaimResponseResultDTO extends BaseDTO {
    public function __construct(
        public readonly array $results,
    ) {}

    public static function fromArray(array $response): static {
        $rows = $response['results'] ?? [];
        $results = [];

        foreach ($rows as $row) {
            $results[] = ODataMedicalClaimResponseResultItemDTO::fromArray($row);
        }

        return new static(
            results: $results,
        );
    }
}
