<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrawingDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'drawing_number',
        'drawing_title',
        'revision',
        'status',
        'notes',
        'reviewed_by',
        'review_date',
    ];

    /**
     * Relasi: Setiap detail gambar dimiliki oleh satu dokumen (surat pengantar).
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Relasi: Setiap detail gambar bisa terhubung dengan banyak item pekerjaan (RAB).
     */
    public function rabItems()
    {
        return $this->belongsToMany(RabItem::class, 'document_rab_item')->withPivot('completion_status');
    }
}