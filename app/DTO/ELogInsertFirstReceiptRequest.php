<?php

namespace App\DTO;

class ELogInsertFirstReceiptRequestDTO extends BaseDTO {
    public function __construct(
        public string $noMedic,
    ) {}
}
