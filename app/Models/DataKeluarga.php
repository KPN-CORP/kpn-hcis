<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKeluarga extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nik',
        'nama',
        'hubungan',
        'tanggal_lahir',
        'umur',
        'status',
    ];

    protected $table = 'data_keluarga';
}
