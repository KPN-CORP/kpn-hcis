<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependents extends Model
{
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    protected $table = 'dependents';

    protected $fillable = [
        'id',
        'employee_id',
        'name',
        'array_id',
        'first_name',
        'middle_name',
        'last_name',
        'relation_type',
        'contact_details',
        'phone',
        'date_of_birth',
        'nationality',
        'updated_on',
        'jobs',
        'gender',
        'no_bpjs',
        'education',
    ];
}
