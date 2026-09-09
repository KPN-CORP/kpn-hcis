<?php

namespace App\DTO;

class ELogLastStatusDetailRequestDTO extends BaseDTO {
    public function __construct(
        public string $no_receipt_doc,
    ) {}
}
