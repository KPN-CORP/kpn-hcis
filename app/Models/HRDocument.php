<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HRDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'letter_name',
        'template_path',
        'variables',
        'created_by',
        'employee_id'
    ];

    protected $casts = [
        'variables' => 'array'
    ];

    protected $table = 'hr_documents';
}
