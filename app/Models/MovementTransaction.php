<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovementTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'movement_transactions';

    protected $fillable = [
        'employee_id',
        'from',
        'to',
        'bu_code',
        'bu_name',
        'designation_code',
        'designation_name',
        'employee_name',
        'group_company',
        'is_demotion',
        'is_promotion',
        'unit_code',
        'unit_name',
    ];

    protected $dates = [
        'form',
        'to',
        'deleted_at',
    ];
}
