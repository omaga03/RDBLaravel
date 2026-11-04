<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user,email'],
            'telegram_chat_id' => ['nullable', 'string', 'max:64'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:auth_item,name'],
            'researcher_id' => ['nullable', 'exists:rdb_researcher,researcher_id'],
            'status' => ['required', 'integer'],
        ];
    }
}
