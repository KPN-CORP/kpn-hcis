<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CATransaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id',
        'type_ca',
        'no_ca',
        'no_sppd',
        'user_id',
        'unit',
        'contribution_level_code',
        'destination',
        'ca_needs',
        'start_date',
        'end_date',
        'date_required',
        'ca_paid_date',
        'detail_ca',
        'total_ca',
        'total_real',
        'total_cost',
        'approval_status',
        'approval_sett',
        'approval_extend',
        'total_days',
        'created_by',
        'caonly',
        'by_admin',
    ];
    protected $table = 'ca_transactions';

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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'id');
    }
    public function approvals()
    {
        return $this->hasMany(ca_approval::class, 'id', 'ca_id');
    }
    public function statusReqEmployee()
    {
        return $this->belongsTo(Employee::class, 'status_id', 'employee_id');
    }
    public function statusSettEmployee()
    {
        return $this->belongsTo(Employee::class, 'sett_id', 'employee_id');
    }
    public function statusExtendEmployee()
    {
        return $this->belongsTo(Employee::class, 'extend_id', 'employee_id');
    }

    public function companies()
    {
        return $this->belongsTo(Company::class, 'contribution_level_code', 'contribution_level_code');
    }
}
