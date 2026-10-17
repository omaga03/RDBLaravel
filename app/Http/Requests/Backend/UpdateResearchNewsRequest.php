<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchNewsRequest extends FormRequest
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
            'news_name' => 'required|string',
            'news_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // Max 50MB
            'news_id' => 'nullable|integer', // Auto-generated or provided
            'news_type' => 'nullable|string|max:255',
            'news_date' => 'nullable|date',
            'news_event_start' => 'nullable|date',
            'news_event_end' => 'nullable|date',
            'news_event_guarantee' => 'nullable|string|max:255',
            'news_detail' => 'nullable|string',
            'news_reference' => 'nullable|string',
            'news_link' => 'nullable|url|max:255',
            'news_count' => 'nullable|integer',
        ];
    }
}
