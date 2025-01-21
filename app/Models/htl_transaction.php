<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class htl_transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_htl', 'user_id', 'no_sppd', 'nama_htl', 'lokasi_htl', 'jmlkmr_htl', 'bed_htl', 'tgl_masuk_htl', 'tgl_keluar_htl', 'start_date', 'end_date', 'date_required', 'detail_ca', 'total_ca', 'total_real', 'total_cost', 'approval_status', 'approval_sett', 'approval_extend'
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


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'id');
    }
}
