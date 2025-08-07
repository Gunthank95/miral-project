<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkItemMaterialNeed extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_item_id',
        'material_id',
        'coefficient',
    ];

    /**
     * Mendapatkan data material yang terhubung dengan kebutuhan ini.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Mendapatkan data item pekerjaan yang terhubung dengan kebutuhan ini.
     */
    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }
}