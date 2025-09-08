<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'package_id',
		'user_id',
		'submitted_to', // <-- Diubah
		'category',
		'requires_approval',
		'title',
		'document_number',      // <-- TAMBAHKAN
		'drawing_numbers',      // <-- TAMBAHKAN
		'addressed_to', 
		'name',
		'file_path',
		'revision',
		'status',
		'notes', // <-- Ditambahkan
		'parent_id',
	];

    /**
     * Mendapatkan data user yang mengupload dokumen.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
	
	public function rabItems()
	{
		return $this->belongsToMany(RabItem::class, 'document_rab_item');
	}
	
	/**
     * TAMBAHKAN: Fungsi untuk relasi ke Riwayat Persetujuan.
     */
    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class);
    }
	
	/**
	 * Relasi: Setiap dokumen (surat pengantar) memiliki banyak detail gambar.
	 */
	public function drawingDetails()
	{
		return $this->hasMany(DrawingDetail::class);
	}
	
	/**
	 * Relasi: Setiap dokumen (surat pengantar) memiliki banyak file lampiran.
	 */
	public function files()
	{
		return $this->hasMany(DocumentFile::class);
	}
	
	public function updateOverallStatus()
	{
		// Jika tidak ada detail gambar sama sekali, tidak melakukan apa-apa.
		if ($this->drawingDetails()->count() == 0) {
			return;
		}

		$statuses = $this->drawingDetails()->pluck('status')->unique();

		if ($statuses->contains('rejected')) {
			$this->status = 'rejected';
		} elseif ($statuses->contains('revision')) {
			$this->status = 'revision';
		} elseif ($statuses->count() === 1 && $statuses->first() === 'approved') {
			// Hanya jika SEMUA status adalah 'approved'
			$this->status = 'approved';
		} else {
			// Jika masih ada yang 'pending' atau kombinasi lain
			$this->status = 'pending';
		}

		$this->save();
	}
}