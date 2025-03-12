<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPriority extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'manager_l1_id',
        'manager_l2_id',
    ];

    protected $table = 'approval_priority';
}
