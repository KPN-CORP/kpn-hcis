<?php

namespace App\DTO;

class ELogInsertFirstReceiptResponseDTO extends BaseDTO {
    public function __construct(
        public string $noMedic,
    ) {}
}
