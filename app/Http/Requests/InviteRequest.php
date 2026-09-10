<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/service
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        // Super Admin - must provide manager IDs (org_admin_id OR project_manager_id)
        if ($user->isSuperAdmin()) {
            $rules['role'] = [
                'nullable',
                'string',
                Rule::in([
                    UserRole::SUPER_ADMIN->value,
                    UserRole::ORGANIZATION_ADMIN->value,
                    UserRole::PROJECT_MANAGER->value,
                    UserRole::MEMBER->value,
                ]),
            ];
            // Manager-based assignment ONLY
            $rules['org_admin_id'] = ['nullable', 'uuid', 'exists:users,id'];
            $rules['project_manager_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }
        // Organization Admin - assigns to Project Manager or Member
        elseif ($user->isOrgAdmin()) {
            $rules['role'] = [
                'nullable',
                'string',
                Rule::in([
                    UserRole::PROJECT_MANAGER->value,
                    UserRole::MEMBER->value,
                ]),
            ];
            // Org Admin assigns to PM (provide PM ID)
            $rules['project_manager_id'] = ['nullable', 'uuid', 'exists:users,id'];
            // OR assigns to existing member under a project
            $rules['member_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }
        // Project Manager - assigns to team member
        elseif ($user->isProjectManager()) {
            // PM assigns to a specific team member (optional)
            $rules['member_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'role.required' => 'Role is required.',
            'role.in' => 'Invalid role selected.',
            'org_admin_id.exists' => 'Selected Organization Admin does not exist.',
            'project_manager_id.exists' => 'Selected Project Manager does not exist.',
            'member_id.exists' => 'Selected Member does not exist.',
        ];
    }
}
