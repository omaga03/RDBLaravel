<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbTrainingRegisterRequest extends FormRequest
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
        // The primary key for RdbTrainingRegister is 'treg_id'
        // Assuming the route parameter is 'id' and represents the treg_id
        $treg_id = $this->route('id');

        return [
            'tra_id' => 'nullable|integer|exists:rdb_training,tra_id',
            'treg_perfix' => 'nullable|string|max:50',
            'treg_name' => 'required|string|max:255',
            'treg_department' => 'nullable|string|max:255',
            'treg_position' => 'nullable|string|max:255',
            'treg_address' => 'nullable|string',
            'treg_tel' => 'nullable|string|max:50',
            'treg_email' => 'nullable|email|max:255',
            'treg_session' => 'nullable|integer',
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
            'treg_name.required' => 'กรุณาระบุชื่อผู้ลงทะเบียน',
            'tra_id.exists' => 'การอบรมไม่ถูกต้อง',
            'treg_email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        ];
    }
}
