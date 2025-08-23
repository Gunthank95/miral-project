<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyLogRequest extends FormRequest
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
        ];
    }
}