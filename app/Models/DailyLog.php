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
        'user_id',
        'log_date',
        'progress_volume',
        'manpower_count',
        'notes',
    ];

    public function rabItem()
    {
        return $this->belongsTo(RabItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materials()
    {
        return $this->hasMany(DailyLogMaterial::class);
    }

    public function equipment()
    {
        return $this->hasMany(DailyLogEquipment::class);
    }

    /**
     * FUNGSI YANG HILANG: Mendapatkan data foto untuk aktivitas ini.
     */
    public function photos()
    {
        return $this->hasMany(DailyLogPhoto::class);
    }
	
	public function report()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
}