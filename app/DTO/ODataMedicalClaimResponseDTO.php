<?php

namespace App\DTO;

class ODataMedicalClaimResponseDTO extends BaseDTO {
    public function __construct(
        public readonly array $data,
    ) {}

    public static function fromArray(array $response): static {
        $rows = $response['data'] ?? [];
        $data = [];

        foreach ($rows as $row) {
            $data[] = ODataMedicalClaimResponseDataDTO::fromArray($row);
        }

        return new static(
            data: $data,
        );
    }
}

// OLD LOGIC

// <?php

// namespace App\DTO;

// class ODataMedicalClaimResponseDTO extends BaseDTO {
//     public function __construct(
//         public readonly ODataMedicalClaimResponseResultDTO $d,
//     ) {}

//     public static function fromArray(array $response): static {
//         return new static(
//             d: ODataMedicalClaimResponseResultDTO::fromArray($response['d'] ?? []),
//         );
//     }
// }
