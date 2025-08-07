<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'parent_id',
        'item_number',
        'item_name',
        'volume',
        'unit',
        'unit_price',
        'weighting',
    ];

    /**
     * FUNGSI YANG HILANG: Mendapatkan data induk (parent) dari item ini.
     */
    public function parent()
    {
        return $this->belongsTo(RabItem::class, 'parent_id');
    }

    /**
     * Mendapatkan data anak (children) dari item ini.
     */
    public function children()
    {
        return $this->hasMany(RabItem::class, 'parent_id');
    }
}