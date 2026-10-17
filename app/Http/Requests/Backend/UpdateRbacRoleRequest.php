<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRbacRoleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // The route model binding gives us the 'item' object.
        $role = $this->route('item');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('auth_item')->ignore($role->name, 'name')
            ],
            'description' => 'nullable|string',
        ];
    }
}
