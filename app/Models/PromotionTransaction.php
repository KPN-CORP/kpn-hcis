<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'promotion_transactions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'employee_id',
        'from',
        'to',
        'employee_name',
        'is_promotion',
        'job_level',
        'job_level_code',
    ];

    protected $dates = [
        'from',
        'to',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
