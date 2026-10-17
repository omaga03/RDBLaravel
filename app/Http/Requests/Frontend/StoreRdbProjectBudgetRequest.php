<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectBudgetRequest extends FormRequest
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
        return [
            'pro_id' => 'nullable|integer|exists:rdb_project,pro_id',
            'ckb_annuity' => 'nullable|numeric',
            'ckb_note' => 'nullable|string',
            'ckb_status' => 'nullable|integer',
            // 'user_created', 'user_updated', 'created_at', 'updated_at' are handled by controller
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
            'pro_id.exists' => 'โครงการไม่ถูกต้อง',
            'ckb_annuity.numeric' => 'งบประมาณต้องเป็นตัวเลข',
        ];
    }
}
