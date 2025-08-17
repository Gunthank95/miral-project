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
		'name',
		'document_number', // <-- Ditambahkan
		'file_path',
		'revision',
		'status',
		'notes', // <-- Ditambahkan
	];

    /**
     * Mendapatkan data user yang mengupload dokumen.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}