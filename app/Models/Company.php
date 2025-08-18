<?php
// GANTI: app/Models/Company.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // TAMBAHKAN

class Company extends Model
{
    use HasFactory;
	
	protected $fillable = [
        'name', 
        'type',
        'address',
        'phone_number',
        'email',
        'logo_path', // TAMBAHKAN
    ];

    // TAMBAHKAN: Accessor untuk URL logo
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }

        // URL ke gambar placeholder jika tidak ada logo
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=EBF4FF&color=76A9FA';
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }
}