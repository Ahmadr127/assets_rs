<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MappingConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'mapping.required' => 'Konfigurasi mapping harus diisi',
            'mapping.array' => 'Format mapping tidak valid',
        ];
    }
}
