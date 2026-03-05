<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use SoftDeletes;

    protected $table = 'certifications';

    protected $fillable = [
        'array_id',
        'employee_id',
        'employee_fullname',
        'certification_completion_date',
        'certification_document',
        'certification_expiry_date',
        'certification_issue_date',
        'certification_name',
        'certification_number',
        'created_on',
        'description',
        'transaction_status',
        'update_on',
    ];

    protected $dates = [
        'certification_completion_date',
        'certification_expiry_date',
        'certification_issue_date',
        'created_on',
        'update_on',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
