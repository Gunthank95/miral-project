<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    /**
     * Mendefinisikan relasi bahwa sebuah Paket "milik" sebuah Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mendefinisikan relasi bahwa sebuah Paket memiliki "banyak" Item RAB.
     */
    public function rabItems()
    {
        return $this->hasMany(RabItem::class, 'package_id');
    }

    /**
     * Mendefinisikan relasi bahwa sebuah Paket memiliki "banyak" Laporan Harian.
     */
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * Mendefinisikan relasi bahwa sebuah Paket memiliki "banyak" Dokumen.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
	
	protected $fillable = ['project_id', 'name'];
	
	// TAMBAHKAN FUNGSI BARU DI SINI
    /**
     * Get all of the schedules for the Package
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}