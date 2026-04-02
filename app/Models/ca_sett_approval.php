<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ca_sett_approval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        // Kolom-kolom lainnya,
        'ca_id',
        'role_id',
        'role_name',
        'employee_id',
        'layer',
        'approval_status',
        'approved_at',
        'reject_info',
        'by_admin',
        'admin_id',
        'deleted_at',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKey()
    {
        return encrypt($this->getKey());
    }

    public static function findByRouteKey($key)
    {
        try {
            $id = decrypt($key);
            return self::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function statusReqEmployee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    public function oldEmployee()
    {
        return $this->belongsTo(Employee::class, 'old_employee_id', 'employee_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }

    public function employee_admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'employee_id');
    }

    public function caTransaction()
    {
        return $this->belongsTo(CATransaction::class, 'ca_id', 'id');
    }
}
