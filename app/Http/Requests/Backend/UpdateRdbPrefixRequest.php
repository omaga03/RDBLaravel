<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbPrefixRequest extends FormRequest
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
        $prefixId = $this->route('id'); // Assuming route parameter is 'id'

        return [
            'prefix_nameTH' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rdb_prefix', 'prefix_nameTH')->ignore($prefixId, 'prefix_id'),
            ],
            'prefix_nameEN' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('rdb_prefix', 'prefix_nameEN')->ignore($prefixId, 'prefix_id'),
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
            'prefix_nameTH.unique' => 'คำนำหน้าภาษาไทยนี้มีอยู่ในระบบแล้ว',
            'prefix_nameEN.unique' => 'คำนำหน้าภาษาอังกฤษนี้มีอยู่ในระบบแล้ว',
        ];
    }
}