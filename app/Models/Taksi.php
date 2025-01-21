<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Taksi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'id');
    }
    public function businessTrip()
    {
        return $this->belongsTo(BusinessTrip::class, 'user_id', 'user_id');
    }
    public function getManager1FullnameAttribute()
    {
        // Get the associated BusinessTrip record
        $businessTrip = $this->businessTrip;
        if ($businessTrip && $businessTrip->manager1) {
            return $businessTrip->manager1->fullname;
        }
        return '-';
    }

    // Relationship to Employee through BusinessTrip for Manager 2
    public function getManager2FullnameAttribute()
    {
        // Get the associated BusinessTrip record
        $businessTrip = $this->businessTrip;
        if ($businessTrip && $businessTrip->manager2) {
            return $businessTrip->manager2->fullname;
        }
        return '-';
    }
    public function latestApprovalL1()
    {
        return $this->hasOne(TaksiApproval::class, 'vt_id', 'id')
            ->where('layer', 1)
            ->where('approval_status', 'Pending L2')
            ->latest('approved_at');
    }
    public function latestApprovalL2()
    {
        return $this->hasOne(TaksiApproval::class, 'vt_id', 'id')
            ->where('layer', 2)
            ->where('approval_status', 'Approved')
            ->latest('approved_at');
    }
    protected $fillable = [
        'id',
        'no_vt',
        'no_sppd',
        'user_id',
        'unit',
        'nominal_vt',
        'keeper_vt',
        'approval_status',
    ];
    protected $table = 'vt_transaction';
}
