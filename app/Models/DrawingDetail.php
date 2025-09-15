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
	
	// ================================================================= //
    // ============== TAMBAHKAN DUA METHOD DI BAWAH INI ================ //
    // ================================================================= //

    /**
     * Mendapatkan deskripsi status gambar berdasarkan status dokumen induknya.
     *
     * @param \App\Models\Document $document
     * @return string
     */
    public function getStatusDescription(Document $document): string
    {
        // Jika dokumen induk berstatus 'revision', status gambar individu menjadi relevan.
        if ($document->status === 'revision') {
            switch ($this->status) {
                case 'revision':
                    return 'Butuh Revisi';
                case 'approved':
                    return 'Sudah OK';
                case 'rejected':
                    return 'Ditolak';
                default:
                    return 'Menunggu Review';
            }
        }

        // Untuk status dokumen lainnya, status gambar mengikuti status dokumen.
        switch ($document->status) {
            case 'pending':
                return 'Submitted';
            case 'menunggu_persetujuan_owner':
                return 'MK Approved';
            case 'approved':
                return 'Owner Approved';
            case 'rejected':
                return 'Ditolak';
            default:
                return ucfirst($document->status);
        }
    }

    /**
     * Mendapatkan kelas CSS untuk badge status gambar.
     *
     * @param \App\Models\Document $document
     * @return string
     */
    public function getStatusBadgeClass(Document $document): string
    {
        if ($document->status === 'revision') {
            switch ($this->status) {
                case 'revision':
                    return 'bg-yellow-100 text-yellow-800';
                case 'approved':
                    return 'bg-green-100 text-green-800';
                case 'rejected':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        switch ($document->status) {
            case 'pending':
                return 'bg-gray-100 text-gray-800';
            case 'menunggu_persetujuan_owner':
                return 'bg-blue-100 text-blue-800';
            case 'approved':
                return 'bg-green-100 text-green-800 font-semibold';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}