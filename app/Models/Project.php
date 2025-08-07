<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * Mendefinisikan relasi bahwa sebuah Project memiliki "banyak" Paket.
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}