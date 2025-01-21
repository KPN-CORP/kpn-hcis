<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bt_attendance_backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'date',
        'no_sppd',
        'employee_id',
        'name',
        'shift_name',
        'shift_in',
        'shift_out',
        'policy_name',
        'assigned_weekly_off',
        'clock_in',
        'clock_out',
        'edit_comment',
        'backup_status',
        'update_db'
    ];
}