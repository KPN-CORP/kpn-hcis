<?php

namespace App\DTO;

class ELogLoginResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly string $token,
    ) {}
}
