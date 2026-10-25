<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StorePublishedRequest extends FormRequest
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
        if ($this->has('pub_date') && $this->pub_date !== null && $this->pub_date !== '') {
            $this->merge([
                'pub_date' => \App\Helpers\ThaiDateHelper::toIsoDate($this->pub_date),
            ]);
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
            'pub_name' => 'required|string',
            'pub_file' => 'nullable|file|mimes:pdf|max:20480',
            'pub_budget' => 'required|numeric',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id',
            'pubtype_id' => 'required|exists:rdb_published_type,pubtype_id',
            'pub_date' => 'required|date',
            'pub_score' => 'required|numeric',
        ];
    }
}
