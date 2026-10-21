<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;
        $currentUser = $this->user();
        $canManageRoles = $currentUser && ($currentUser->hasRole('The Creator') || $currentUser->hasRole('Admin') || $currentUser->hasPermission('User Manager'));

        $rules = [
            'username' => ['required', 'string', 'max:255', Rule::unique('user')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('user')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
        ];

        if ($canManageRoles) {
            $rules['role'] = ['required', 'string', 'exists:auth_item,name'];
            $rules['researcher_id'] = ['nullable', 'exists:rdb_researcher,researcher_id'];
            $rules['status'] = ['required', 'integer'];
        }

        return $rules;
    }
}
