<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTrip extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'id');
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'user_id', 'user_id');
    }
    public function tiket()
    {
        return $this->belongsTo(Tiket::class, 'user_id', 'user_id');
    }
    public function taksi()
    {
        return $this->belongsTo(Taksi::class, 'user_id', 'user_id');
    }
    public function checkCompany()
    {
        return $this->belongsTo(Company::class, 'bb_perusahaan', 'contribution_level_code');
    }
    public function manager1()
    {
        return $this->belongsTo(Employee::class, 'manager_l1_id', 'employee_id');
    }

    public function manager2()
    {
        return $this->belongsTo(Employee::class, 'manager_l2_id', 'employee_id');
    }

    public function approvals()
    {
        return $this->hasMany(BTApproval::class, 'bt_id', 'id');
    }
    // BusinessTrip.php
    public function latestApprovalL1()
    {
        return $this->hasOne(BTApproval::class, 'bt_id', 'id')
            ->where('layer', 1)
            ->where('approval_status', 'Pending L2')
            ->latest('approved_at')
            ->with('manager1');
    }

   public function latestApprovalL2()
    {
        return $this->hasOne(BTApproval::class, 'bt_id', 'id')
            ->where('layer', 2)
            ->where('approval_status', 'Approved')
            ->latest('approved_at')
            ->with('manager2');
    }

    // BusinessTrip.php
    public function latestApprovalDecL1()
    {
        return $this->hasOne(BTApproval::class, 'bt_id', 'id')
            ->where('layer', 1)
            ->where('approval_status', 'Declaration L2')
            ->latest('approved_at')
            ->with('manager1');
    }

   public function latestApprovalDecL2()
    {
        return $this->hasOne(BTApproval::class, 'bt_id', 'id')
            ->where('layer', 2)
            ->where('approval_status', 'Declaration Approved')
            ->latest('approved_at')
            ->with('manager2');
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'jns_dinas',
        'nama',
        'no_sppd',
        'unit_1',
        'atasan_1',
        'email_1',
        'unit_2',
        'atasan_2',
        'email_2',
        'divisi',
        'mulai',
        'kembali',
        'tujuan',
        'keperluan',
        'bb_perusahaan',
        'norek_krywn',
        'nama_pemilik_rek',
        'nama_bank',
        'ca',
        'tiket',
        'hotel',
        'mess',
        'taksi',
        'id_ca',
        'id_tiket',
        'id_hotel',
        'id_taksi',
        'status',
        'doc_at',
        'return_at',
        'verified_at',
        'manager_l1_id',
        'manager_l2_id',
        'deleted_at',
        'update_db',

    ];

    protected $table = 'bt_transaction';
}
