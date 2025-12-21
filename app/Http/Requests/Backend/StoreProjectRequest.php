<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'pro_nameTH' => 'required',
            'year_id' => 'required',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id',
            'ratio' => 'required|numeric|min:0|max:100',
            'position_id' => 'required|exists:rdb_project_position,position_id',
        ];
    }
}
