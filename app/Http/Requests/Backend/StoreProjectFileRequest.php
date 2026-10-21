<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png',
            'rf_note' => 'nullable|string',
            'rf_files_show' => 'nullable|boolean',
        ];
    }
}
