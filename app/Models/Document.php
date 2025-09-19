<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class)->latest();
    }

    /**
     * PERBAIKAN UTAMA DI SINI: Menambahkan withPivot() untuk mengambil status kelengkapan.
     */
    public function rabItems()
    {
        return $this->belongsToMany(RabItem::class, 'document_rab_item')
                    ->withPivot('completion_status'); // <-- INI KUNCINYA
    }

    public function drawingDetails()
    {
        return $this->hasMany(DrawingDetail::class);
    }
    
    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }
    
    // Relasi untuk dokumen revisi
    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function revisions()
    {
        return $this->hasMany(Document::class, 'parent_document_id', 'id');
    }
	
	/**
	 * Mendefinisikan relasi ke model DocumentInternalReview.
	 * Satu dokumen bisa memiliki banyak review internal.
	 */
	public function internalReviews()
	{
		return $this->hasMany(DocumentInternalReview::class);
	}
}