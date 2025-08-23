<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodicReportFilterRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat request ini.
     *
     * @return bool
     */
    public function authorize()
    {
        // GANTI: Ubah menjadi true agar semua pengguna yang sudah login bisa mengaksesnya.
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // TAMBAHKAN: Pindahkan semua aturan validasi dari controller ke sini.
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filter' => 'nullable|string|in:all,this_period,until_now',
        ];
    }
}