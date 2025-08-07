<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Hanya ada SATU deklarasi class
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
     * (Fungsi ini saya pindahkan ke dalam class yang benar)
     */
    public function rabItems()
    {
        return $this->hasMany(RabItem::class, 'package_id');
    }
	
	public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }
}