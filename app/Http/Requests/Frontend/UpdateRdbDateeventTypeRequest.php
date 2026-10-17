<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDateeventTypeRequest extends FormRequest
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
        // The primary key for RdbDateeventType is 'evt_id'
        // Assuming the route parameter is 'id' and represents the evt_id
        $evt_id = $this->route('id');

        return [
            'evt_name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_dateevent_type', 'evt_name')->ignore($evt_id, 'evt_id'),
            ],
            'evt_color' => 'nullable|string|max:20',
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
            'evt_name.unique' => 'ชื่อประเภทกิจกรรมนี้มีอยู่ในระบบแล้ว',
            'evt_name.required' => 'กรุณาระบุชื่อประเภทกิจกรรม',
        ];
    }
}
