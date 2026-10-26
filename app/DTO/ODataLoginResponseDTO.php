<?php

namespace App\DTO;

class ODataLoginResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $access_token,
        public readonly string $token_type,
        public readonly int $expires_in,
    ) {}
}
