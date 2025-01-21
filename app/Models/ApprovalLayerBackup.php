<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLayerBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'approver_id', 'layer','updated_by', 'created_at', 'updated_at'
    ];
}
