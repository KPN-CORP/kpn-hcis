<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ELogCompanyExtSys extends SafeOracleModel
{
    protected $table = 'COMPANY_EXTSYS';

    public function company()
    {
        return $this->belongsTo(ELogCompany::class, 'COMPANY', 'ID');
    }
}
