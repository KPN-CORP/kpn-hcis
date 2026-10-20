<?php

namespace App\DTO;

class ODataMedicalClaimResponseDTO extends BaseDTO {
    public function __construct(
        public readonly ODataMedicalClaimResponseResultDTO $d,
    ) {}

    public static function fromArray(array $response): static {
        return new static(
            d: ODataMedicalClaimResponseResultDTO::fromArray($response['d'] ?? []),
        );
    }
}
