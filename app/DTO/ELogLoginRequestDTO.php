<?php

namespace App\DTO;

class ELogLoginRequestDTO extends BaseDTO {
    public function __construct(
        public string $username,
        public string $password,
    ) {}
}
