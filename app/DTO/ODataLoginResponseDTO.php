<?php

namespace App\DTO;

class ODataLoginResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
    ) {}
}
