<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BTApproval extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;
    public function businessTrip()
    {
        return $this->belongsTo(BusinessTrip::class, 'bt_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    public function manager1()
    {
        return $this->belongsTo(Employee::class, 'manager_l1_id', 'employee_id');
    }

    public function manager2()
    {
        return $this->belongsTo(Employee::class, 'manager_l2_id', 'employee_id');
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'bt_id',
        'role_id',
        'role_name',
        'layer',
        'approval_status',
        'approved_at',
        'employee_id',

    ];
    protected $table = 'bt_approval';
}
