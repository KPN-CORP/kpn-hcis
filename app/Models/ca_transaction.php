<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ca_transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type_ca',
        'no_ca',
        'no_sppd',
        'user_id',
        'unit',
        'contribution_level_code',
        'destination',
        'ca_needs',
        'start_date',
        'end_date',
        'date_required',
        'ca_paid_date',
        'detail_ca',
        'total_ca',
        'total_real',
        'total_cost',
        'approval_status',
        'approval_sett',
        'approval_extend',
        'paid_date',
        'ca_status'
    ];

    public function getRouteKey()
    {
        return encrypt($this->getKey());
    }
    
}
