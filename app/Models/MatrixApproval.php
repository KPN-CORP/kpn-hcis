<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatrixApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        // Kolom-kolom lainnya,
        'group_company','contribution_level_code','modul','condt','layer','desc','role_id','employee_id'
    ];
}
