<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbResearcherStatusRequest extends FormRequest
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
            'restatus_name' => 'required|string|max:255|unique:rdb_researcher_status,restatus_name',
        ];
    }

    public function messages(): array
    {
        return [
            'restatus_name.unique' => 'ชื่อสถานะนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
