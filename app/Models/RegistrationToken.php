<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationToken extends Model
{
    use HasFactory;

    protected $table = 'registration_tokens';

    protected $fillable = [
        'token',
        'email',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}