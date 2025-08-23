<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRabItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:rab_items,id',
            'item_number' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'volume' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
        ];
    }
}