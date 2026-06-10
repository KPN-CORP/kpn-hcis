<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaksiApproval extends Model
{
    use HasFactory, Hasuuids;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'vt_id',
        'role_id',
        'role_name',
        'employee_id',
        'layer',
        'approval_status',
        'approved_at',
        'reject_info',
    ];
    protected $table = 'vt_approvals';

    public function taksi()
    {
        return $this->belongsTo(Taksi::class, 'id', 'vt_id');
    }

    public function adminEmployeeById()
    {
        return $this->belongsTo(Employee::class, 'admin_id', 'id');
    }

    public function adminEmployeeByEmployeeId()
    {
        return $this->belongsTo(Employee::class, 'admin_id', 'employee_id');
    }

    public function getAdminEmployeeAttribute()
    {
        return $this->adminEmployeeById
            ?? $this->adminEmployeeByEmployeeId;
    }
}
