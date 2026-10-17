<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectFileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'required|file|max:51200',
            'rf_note' => 'nullable|string',
            'rf_files_show' => 'nullable|boolean',
        ];
    }
}
