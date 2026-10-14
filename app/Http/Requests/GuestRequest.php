<?php

namespace App\Http\Requests;

use App\Models\Guest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuestRequest extends FormRequest
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
        $guestId = $this->route('guest')?->id ?? $this->route('guest');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('guests', 'email')->ignore($guestId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'id_or_passport' => ['nullable', 'string', 'max:100'],
            'vip_level' => ['required', Rule::in([Guest::VIP_STANDARD, Guest::VIP_VIP, Guest::VIP_VVIP])],
            'special_requests' => ['nullable', 'string'],
        ];
    }
}
