<?php
// GANTI file Company.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
	
	protected $fillable = ['name', 'type'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // TAMBAHKAN: Relasi baru ke personnel
    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }
}