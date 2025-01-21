<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class master_holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        // Kolom-kolom lainnya,
        'id',
        'tanggal_libur',
        'ket',
    ];
    protected $table = 'master_holidays';
}
