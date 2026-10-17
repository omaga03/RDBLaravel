<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbResearcherStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller's Gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // The primary key for RdbResearcherStatus is 'restatus_id'
        // Assuming the route parameter is 'id' and represents the restatus_id
        $restatus_id = $this->route('id');

        return [
            'restatus_name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_researcher_status', 'restatus_name')->ignore($restatus_id, 'restatus_id'),
            ],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'restatus_name.unique' => 'ชื่อสถานะนักวิจัยนี้มีอยู่ในระบบแล้ว',
            'restatus_name.required' => 'กรุณาระบุชื่อสถานะนักวิจัย',
        ];
    }
}
