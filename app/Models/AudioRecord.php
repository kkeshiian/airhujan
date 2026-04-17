<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'device_code',
        'file_path',
        'duration_seconds',
        'device_status',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}
