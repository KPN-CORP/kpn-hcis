<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessApproval extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'mess_id',
        'role_id',
        'role_name',
        'employee_id',
        'layer',
        'approval_status',
        'approved_at',
        'reject_info',
    ];
    protected $table = 'mess_approvals';

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'id', 'mess_id');
    }
    public function employeeId()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
