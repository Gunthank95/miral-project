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
        'rab_item_id', // Pastikan ini ada
        'parent_id',   // Pastikan ini ada
        'task_name',
        'start_date',
        'end_date',
        'progress',
        'dependencies',
        'sort_order', // Pastikan ini ada
    ];

    /**
     * Get the package that the schedule belongs to.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
	
	public function rabItem()
    {
        return $this->belongsTo(RabItem::class, 'rab_item_id');
    }
}