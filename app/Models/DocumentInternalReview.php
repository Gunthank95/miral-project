<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentInternalReview extends Model
{
    use HasFactory;

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'user_id',
        'status',
        'notes',
        'drawing_reviews',
    ];

    /**
     * Tipe data atribut yang perlu di-casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'drawing_reviews' => 'array', // Otomatis mengubah JSON menjadi array PHP
    ];

    /**
     * Mendefinisikan relasi ke model Document.
     * Setiap review internal pasti dimiliki oleh satu dokumen.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Mendefinisikan relasi ke model User.
     * Setiap review internal pasti dimiliki oleh satu pengguna.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}