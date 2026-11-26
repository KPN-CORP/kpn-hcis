<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeTripPlanHistory extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    protected $table = 'ht_plan_histories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'ht_plan_id',
        'employee_id_before',
        'employee_id_after',
        'name_before',
        'name_after',
        'relation_type_before',
        'relation_type_after',
        'quota_before',
        'quota_after',
        'period_before',
        'period_after',
        'last_generate_before',
        'last_generate_after',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
