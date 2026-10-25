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

    protected function prepareForValidation(): void
    {
        foreach (['news_date', 'news_event_start', 'news_event_end'] as $field) {
            if ($this->has($field) && $this->input($field) !== null && $this->input($field) !== '') {
                $this->merge([$field => \App\Helpers\ThaiDateHelper::toIsoDate($this->input($field))]);
            }
        }
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
            'news_event_end' => 'nullable|date|after_or_equal:news_event_start',
            'news_event_guarantee' => 'nullable|string|max:255',
            'news_detail' => 'nullable|string',
            'news_reference' => 'nullable|string',
            'news_link' => 'nullable|url|max:255',
            'news_count' => 'nullable|integer',
        ];
    }
}
