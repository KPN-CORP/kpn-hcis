<?php

namespace App\DTO;

class ELogHistoryRequestDTO extends BaseDTO {
    public function __construct(
        public string $no_receipt_doc,
    ) {}
}
