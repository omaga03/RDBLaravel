<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublishedRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pub_name' => 'required|string',
            'pub_file' => 'nullable|file|mimes:pdf|max:20480',
            'pub_budget' => 'required|numeric',
            // researcher_id is optional in update
            'pubtype_id' => 'required|exists:rdb_published_type,pubtype_id',
            'pub_date' => 'required|date',
            'pub_score' => 'required|numeric',
        ];
    }
}
