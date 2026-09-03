<?php

namespace App\DTO;

class ELogInsertFirstReceiptRequestDTO extends BaseDTO {
    public function __construct(
        public string $extsyscompanycode,
        public string $invoice_code,
        public string $no_po,
        public string $vendor,
        public int $amount,
        public int $sisa_over_plafond,
        public int $non_reimbursable_amount,
        public string $nik,
        public string $no_rekening,
        public string $nama_bank,
        public string $cost_center,
        public string $plafond_type,
        public string $notes,
        public string $first_dept,
        public string $created_by,
        public string $inv_date,
        public string $trans_type,
    ) {}

    public function toUpperCaseArray(): array
    {
        return [
            'EXTSYSCOMPANYCODE' => $this->extsyscompanycode,
            'INVOICE_CODE' => $this->invoice_code,
            'NO_PO' => $this->no_po,
            'VENDOR' => $this->vendor,
            'AMOUNT' => $this->amount,
            'SISA_OVER_PLAFOND' => $this->sisa_over_plafond,
            'NON_REIMBURSABLE_AMOUNT' => $this->non_reimbursable_amount,
            'NIK' => $this->nik,
            'NO_REKENING' => $this->no_rekening,
            'NAMA_BANK' => $this->nama_bank,
            'COST_CENTER' => $this->cost_center,
            'plafond_type' => $this->plafond_type,
            'NOTES' => $this->notes,
            'FIRST_DEPT'  => $this->first_dept,
            'CREATED_BY' => $this->created_by,
            'INV_DATE' => $this->inv_date,
            'TRANS_TYPE' => $this->trans_type,
        ];
    }
}
