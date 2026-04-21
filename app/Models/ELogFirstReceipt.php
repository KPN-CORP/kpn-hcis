<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ELogFirstReceipt extends SafeOracleModel
{
    protected $table = 'LOG_FIRSTRECEIPT';

    protected $fillable = [
        'UUID',
        'COMPANY',
        'NO_RECEIPT_DOC',
        'INVOICE_CODE',
        'NO_PO',
        'VENDOR',
        'CURRENCY',
        'AMOUNT',
        'FIRST_DEPT',
        'CREATED_BY',
        'CREATED_AT',
        'NOTES',
        'TOTAL_AMOUNT',
        'TRANS_TYPE',
        'STATUS_PRIORITY',
        'AMOUNT_PPN',
        'PROFORMA',
        'AMT_SUB_TYPE',
        'STATUS_PKP',
        'VAT_CONVERSION',
        'RATE_CURRENCY',
        'INV_DATE',
        'RETENSI',
        'RETENSI_PERCENTAGE',
        'RETENSI_TOTAL',
        'OTHER_CHARGE',
        'TOTALX',
        'SOURCE',
        'IS_RETENSI',
        'POTONGAN_DP',
        'DPP_NILAI_LAIN',
        'PPN_FAKTUR',
        'DPP_PPH',
        'DP_PERCENTAGE'
    ];
}
