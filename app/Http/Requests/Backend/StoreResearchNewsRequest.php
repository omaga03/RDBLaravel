<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ResearchNews; // Required for news_id generation

class StoreResearchNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller's Gate
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Auto-generate con_id if not provided, similar to ResearchConferenceController
        // Check if news_id is in the request and is empty
        if (empty($this->news_id)) {
            $lastNewsId = ResearchNews::max('news_id');
            if ($lastNewsId && $lastNewsId >= 1000000000) {
                $this->merge(['news_id' => $lastNewsId + 1]);
            } else {
                $this->merge(['news_id' => 1000000000]);
            }
        }

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
            'news_id' => 'nullable|integer|unique:research_news,news_id', // news_id is a PK, should be unique
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
