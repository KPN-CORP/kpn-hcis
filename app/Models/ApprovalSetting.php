<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'approval_type',
        'company_name',
        'contribution_level_code',
        'work_area',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $table = 'approval_setting';
}
