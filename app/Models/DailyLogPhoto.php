<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLogPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_log_id',
        'file_path',
    ];
}