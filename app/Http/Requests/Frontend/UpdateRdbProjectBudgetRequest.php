<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectBudgetRequest extends FormRequest
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
        // The primary key for RdbProjectBudget is 'ckb_id'
        // Assuming the route parameter is 'id' and represents the ckb_id
        $ckb_id = $this->route('id');

        return [
            'pro_id' => 'nullable|integer|exists:rdb_project,pro_id',
            'ckb_annuity' => 'nullable|numeric',
            'ckb_note' => 'nullable|string',
            'ckb_status' => 'nullable|integer',
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
