<?php

namespace App\DTO;

class ODataLoginRequestDTO extends BaseDTO {
    public function __construct(
        public string $clientId,
        public string $clientSecret,
    ) {}
}
