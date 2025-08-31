<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;
	
	protected $fillable = [
        'document_id',
        'parent_id',
        'user_id',
        'role',
        'status',
        'notes',
        'reviewed_file_path',
    ];
	
	/**
     * Relasi untuk mendapatkan data induk (pengajuan awal).
     */
    public function parent()
    {
        return $this->belongsTo(DocumentApproval::class, 'parent_id');
    }

    /**
     * Relasi untuk mendapatkan data anak (semua riwayat review).
     */
    public function children()
    {
        return $this->hasMany(DocumentApproval::class, 'parent_id');
    }
}
