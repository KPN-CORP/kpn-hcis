<?php

namespace App\DTO;

class ODataMedicalClaimResponseDTO extends BaseDTO {
    public function __construct(
        public readonly ODataMedicalClaimResponseResultDTO $d,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            d: ODataMedicalClaimResponseResultDTO::fromArray($response['d'] ?? []),
        );
    }
}
