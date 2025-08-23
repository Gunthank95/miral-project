<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rab_item_id' => 'required_without:custom_work_name|nullable|exists:rab_items,id',
            'custom_work_name' => 'required_without:rab_item_id|nullable|string|max:255',
            'progress_volume' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'materials' => 'nullable|array',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.quantity' => 'required|numeric|min:0',
            'materials.*.unit' => 'required|string',
            'equipment' => 'nullable|array',
            'equipment.*.name' => 'required|string',
            'equipment.*.quantity' => 'required|integer|min:1',
        ];
    }
}