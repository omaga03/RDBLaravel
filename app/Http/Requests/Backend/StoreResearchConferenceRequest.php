<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreResearchConferenceRequest extends FormRequest
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
            'con_name' => 'required|string',
            'con_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // Max 50MB
            'con_id' => 'nullable|string|max:20',
            'con_detail' => 'nullable|string',
            'con_even_date' => 'nullable|string|max:255',
            'con_sub_deadline' => 'nullable|string|max:255',
            'con_venue' => 'nullable|string',
            'con_website' => 'nullable|url|max:255',
            'con_site_img' => 'nullable|string|max:255',
            'con_count' => 'nullable|integer',
            'data_show' => 'nullable|boolean',
        ];
    }
}
