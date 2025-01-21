<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Dependent;

class HomeTrip extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;
    protected $table = 'ht_plans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'employee_id',
        'name',
        'relation_type',
        'quota',
        'period',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function dependents()  
    {  
        return $this->hasMany(Dependents::class, 'employee_id', 'employee_id');  
    }  
}
