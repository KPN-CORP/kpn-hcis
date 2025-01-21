<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiketApproval extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    public function employeeId()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    protected $fillable = [
        'id',
        'tkt_id',
        'role_id',
        'role_name',
        'employee_id',
        'layer',
        'approval_status',
        'approved_at',
        'reject_info,',
    ];
    protected $table = 'tkt_approvals';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
