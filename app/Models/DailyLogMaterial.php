<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLogMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_log_id',
        'material_id',
        'quantity',
        'unit',
    ];

    /**
     * FUNGSI BARU: Mendapatkan info detail material dari data master.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}