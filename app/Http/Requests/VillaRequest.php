<?php

namespace App\Http\Requests;

use App\Models\Villa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VillaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $villaId = $this->route('villa')?->id ?? $this->route('villa');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('villas', 'code')->ignore($villaId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'bedrooms' => ['required', 'integer', 'min:1', 'max:50'],
            'bathrooms' => ['required', 'integer', 'min:1', 'max:50'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:100'],
            'area_sqm' => ['nullable', 'integer', 'min:10'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'weekend_price_per_night' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([Villa::STATUS_AVAILABLE, Villa::STATUS_OCCUPIED, Villa::STATUS_CLEANING, Villa::STATUS_MAINTENANCE, Villa::STATUS_RESERVED])],
            'location' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
