<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbyearRequest extends FormRequest
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
            'year_name' => 'required|string|max:4|unique:rdb_year,year_name',
        ];
    }

    public function messages(): array
    {
        return [
            'year_name.unique' => 'ปีงบประมาณนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
