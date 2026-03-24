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
        'company_names',
        'contribution_level_codes',
        'work_areas',
        'hcga_employee_id',
        'ktu_employee_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $table = 'approval_setting';

    public function hcga_employee()
    {
        return $this->belongsTo(Employee::class, 'hcga_employee_id', 'employee_id');
    }

    public function ktu_employee()
    {
        return $this->belongsTo(Employee::class, 'ktu_employee_id', 'employee_id');
    }
}
