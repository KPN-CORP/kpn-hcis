<?php

namespace App\DTO;

class ELogInsertFirstReceiptRequestDTO extends BaseDTO {
    public function __construct(
        public string $extsyscompanycode,
        public string $invoice_code,
        public string $no_po,
        public string $vendor,
        public string $amount,
        public string $notes,
        public string $first_dept,
        public string $created_by,
        public string $inv_date,
        public string $trans_type,
    ) {}
}
