<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbPublishedWorkRequest extends FormRequest
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
        // The primary key for RdbPublishedWork is 'published_id' (composite)
        // Assuming the route parameter is 'id' and represents the primary key
        $id = $this->route('id');

        return [
            // The `published_id` is part of the key and shouldn't be updatable from the form
            'researcher_id' => 'nullable|integer|exists:rdb_researcher,researcher_id',
            'pubta_id' => 'nullable|integer|exists:rdb_published_type_author,pubta_id',
            'pubw_main' => 'nullable|string|max:1',
            'pubw_bud' => 'nullable|numeric',
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
            'researcher_id.exists' => 'นักวิจัยไม่ถูกต้อง',
            'pubta_id.exists' => 'ประเภทผู้แต่งไม่ถูกต้อง',
        ];
    }
}
