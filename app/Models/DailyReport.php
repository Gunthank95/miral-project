<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'user_id',
        'report_date',
    ];

    /**
     * Mendapatkan data user yang membuat laporan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data cuaca untuk laporan ini.
     */
    public function weather()
    {
        return $this->hasMany(DailyReportWeather::class);
    }

    /**
     * Mendapatkan data personil untuk laporan ini.
     */
    public function personnel()
    {
        return $this->hasMany(DailyReportPersonnel::class);
    }

    /**
     * Mendapatkan data aktivitas pekerjaan untuk laporan ini.
     */
    public function activities()
    {
        return $this->hasMany(DailyLog::class);
    }

    /**
     * FUNGSI YANG HILANG: Mendapatkan data paket pekerjaan dari laporan ini.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}