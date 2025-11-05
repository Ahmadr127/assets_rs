<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportConfirmationRequest extends FormRequest
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
            'action' => 'required|string|in:create,update,skip',
            'process_duplicates' => 'nullable|boolean',
            'process_errors' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi harus dipilih',
            'action.in' => 'Aksi tidak valid. Pilih: create, update, atau skip',
        ];
    }
}
