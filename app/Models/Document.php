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
}