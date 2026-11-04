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
            'telegram_chat_id' => ['nullable', 'string', 'max:64'],
            'password' => ['nullable', 'string', 'min:8'],
            'pref_mail_enabled' => ['nullable'],
            'pref_telegram_enabled' => ['nullable'],
            'pref_topic_research_approved' => ['nullable'],
            'pref_topic_research_rejected' => ['nullable'],
            'pref_topic_review_assigned' => ['nullable'],
            'pref_topic_review_final_approved' => ['nullable'],
            'admin_notification_settings_submitted' => ['nullable'],
            'global_telegram_bot_token' => ['nullable', 'string'],
            'global_telegram_admin_chat_id' => ['nullable', 'string'],
            'admin_notify_telegram' => ['nullable'],
            'admin_notify_mail' => ['nullable'],
            'admin_notify_create' => ['nullable'],
            'admin_notify_update' => ['nullable'],
            'admin_notify_delete' => ['nullable'],
            'admin_notify_upload' => ['nullable'],
            'admin_notify_backup' => ['nullable'],
            'admin_notify_auth' => ['nullable'],
            'admin_notify_scheduler' => ['nullable'],
        ];

        if ($canManageRoles) {
            $rules['role'] = ['required', 'string', 'exists:auth_item,name'];
            $rules['researcher_id'] = ['nullable', 'exists:rdb_researcher,researcher_id'];
            $rules['status'] = ['required', 'integer'];
            $rules['onboarding_status'] = ['nullable', 'string', 'in:none,pending,approved,rejected'];
        }

        return $rules;
    }
}
