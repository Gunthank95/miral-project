<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'task_name',
        'start_date',
        'end_date',
        'progress',
        'dependencies',
    ];

    /**
     * Get the package that the schedule belongs to.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}