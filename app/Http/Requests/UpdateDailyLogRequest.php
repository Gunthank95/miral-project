<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    return [
        'rab_item_id' => 'nullable|exists:rab_items,id',
        'custom_work_name' => 'nullable|string|max:255',
        'progress_volume' => 'required|numeric|min:0',
        'manpower' => 'nullable|array',
        'manpower.*.role' => 'required_with:manpower.*.quantity|string|max:255',
        'manpower.*.quantity' => 'required_with:manpower.*.role|integer|min:1',
        
        // --- PERBAIKAN DIMULAI DI SINI ---

        // 1. Array 'materials' sekarang boleh kosong (nullable)
        'materials' => 'nullable|array', 
        // 2. ID Material hanya wajib jika kuantitas diisi, dan sebaliknya
        'materials.*.id' => 'required_with:materials.*.quantity|nullable|exists:materials,id',
        'materials.*.quantity' => 'required_with:materials.*.id|nullable|numeric|min:0',

        // 3. Array 'equipment' sudah benar (nullable), kita hanya pastikan sub-fieldnya juga nullable
        'equipment' => 'nullable|array',
        'equipment.*.name' => 'required_with:equipment.*.quantity|nullable|string|max:255',
        'equipment.*.quantity' => 'required_with:equipment.*.name|nullable|integer|min:1',
        'equipment.*.specification' => 'nullable|string|max:255',

        // --- AKHIR PERBAIKAN ---

        'photos' => 'nullable|array',
        'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
    ];
}
}