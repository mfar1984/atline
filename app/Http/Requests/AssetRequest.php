<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * This is used for UPDATE operations only.
     * CREATE operations use inline validation in controller for bulk create support.
     */
    public function rules(): array
    {
        $assetId = $this->route('inventory')?->id ?? $this->route('asset')?->id;
        
        return [
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'asset_tag' => 'required|string|max:50|unique:assets,asset_tag,' . $assetId,
            'brand_id' => 'nullable|exists:brands,id',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'status' => 'required|in:active,spare,damaged,maintenance,disposed',
            'specs' => 'nullable|array',
            'unit_price' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'assigned_to' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select a project.',
            'category_id.required' => 'Please select a category.',
            'asset_tag.required' => 'Asset Tag/ID is required.',
            'asset_tag.unique' => 'This Asset Tag already exists.',
        ];
    }
}
