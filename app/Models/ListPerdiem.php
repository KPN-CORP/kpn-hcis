<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListPerdiem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade',
        'amount',
        'job_level'
    ];
}
