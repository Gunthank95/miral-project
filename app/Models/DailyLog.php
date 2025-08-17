<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    use HasFactory;

    protected $casts = [
        'log_date' => 'date',
    ];

    protected $fillable = [
        'daily_report_id',
        'package_id',
        'rab_item_id',
        'custom_work_name', // Pastikan ini ada
        'user_id',
        'log_date',
        'progress_volume',
        'manpower_count',
        'notes',
    ];

    public function report()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    public function rabItem()
    {
        return $this->belongsTo(RabItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * FUNGSI PENTING: Mendapatkan data material untuk aktivitas ini.
     */
    public function materials()
    {
        return $this->hasMany(DailyLogMaterial::class);
    }

    /**
     * FUNGSI PENTING: Mendapatkan data peralatan untuk aktivitas ini.
     */
    public function equipment()
    {
        return $this->hasMany(DailyLogEquipment::class);
    }

    /**
     * FUNGSI PENTING: Mendapatkan data foto untuk aktivitas ini.
     */
    public function photos()
    {
        return $this->hasMany(DailyLogPhoto::class);
    }
	
	public function manpower()
	{
		return $this->hasMany(DailyLogManpower::class);
	}
}