<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedProgress extends Model
{
    use HasFactory;

    protected $table = 'planned_progress';

    protected $fillable = [
        'package_id',
        'week_start_date',
        'weight',
    ];
}