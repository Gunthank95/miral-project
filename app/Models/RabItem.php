<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    use HasFactory;
    protected $fillable = ['package_id', 'parent_id', 'item_number', 'item_name', 'unit', 'volume', 'unit_price', 'total_price', 'weighting'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function parent()
    {
        return $this->belongsTo(RabItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RabItem::class, 'parent_id');
    }

    public function dailyLogs()
    {
        return $this->hasMany(DailyLog::class);
    }
    
    /**
     * TAMBAHKAN: Relasi baru untuk mengambil semua induk/leluhur.
     * Ini adalah kunci agar `with('ancestors')` di controller bisa berfungsi.
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }
}