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
     * Only Super Admin, Org Admin, and Project Manager can invite users.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Only Super Admin, Org Admin, and Project Manager can invite
        return $user->isSuperAdmin()
            || $user->isOrgAdmin()
            || $user->isProjectManager();
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

        // Super Admin - can invite any role
        if ($user->isSuperAdmin()) {
            $rules['role'] = [
                'required',
                'string',
                Rule::in([
                    UserRole::SUPER_ADMIN->value,
                    UserRole::ORGANIZATION_ADMIN->value,
                    UserRole::PROJECT_MANAGER->value,
                    UserRole::MEMBER->value,
                ]),
            ];
            $rules['org_admin_id'] = ['nullable', 'uuid', 'exists:users,id'];
            $rules['project_manager_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }
        // Organization Admin - can invite PM or Member
        elseif ($user->isOrgAdmin()) {
            $rules['role'] = [
                'required',
                'string',
                Rule::in([
                    UserRole::PROJECT_MANAGER->value,
                    UserRole::MEMBER->value,
                ]),
            ];
            $rules['project_manager_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }
        // Project Manager - can only invite members
        elseif ($user->isProjectManager()) {
            // Role is optional for PM, defaults to member
            $rules['role'] = ['nullable', 'string', Rule::in([UserRole::MEMBER->value])];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            // Super Admin validation
            if ($user->isSuperAdmin()) {
                $hasOrgAdmin = $this->filled('org_admin_id');
                $hasPM = $this->filled('project_manager_id');
                $role = $this->input('role');

                // Both provided - error
                if ($hasOrgAdmin && $hasPM) {
                    $validator->errors()->add(
                        'org_admin_id',
                        'You cannot provide both org_admin_id and project_manager_id. Please choose one.'
                    );

                    return;
                }

                // Role-based validation
                if ($role === 'member') {
                    // Member role requires project_manager_id
                    if (! $hasPM) {
                        $validator->errors()->add(
                            'project_manager_id',
                            'The project_manager_id field is required when inviting a Member.'
                        );
                    }
                    if ($hasOrgAdmin) {
                        $validator->errors()->add(
                            'org_admin_id',
                            'The org_admin_id field is not allowed when inviting a Member. Use project_manager_id instead.'
                        );
                    }
                } elseif ($role === 'project_manager') {
                    // Project Manager role requires org_admin_id
                    if (! $hasOrgAdmin) {
                        $validator->errors()->add(
                            'org_admin_id',
                            'The org_admin_id field is required when inviting a Project Manager.'
                        );
                    }
                    if ($hasPM) {
                        $validator->errors()->add(
                            'project_manager_id',
                            'The project_manager_id field is not allowed when inviting a Project Manager. Use org_admin_id instead.'
                        );
                    }
                } elseif ($role === 'organization_admin') {
                    // Organization Admin role should NOT have any manager IDs
                    if ($hasOrgAdmin) {
                        $validator->errors()->add(
                            'org_admin_id',
                            'The org_admin_id field is not allowed when inviting an Organization Admin.'
                        );
                    }
                    if ($hasPM) {
                        $validator->errors()->add(
                            'project_manager_id',
                            'The project_manager_id field is not allowed when inviting an Organization Admin.'
                        );
                    }
                }
                // For super_admin role, IDs are optional
            }

            // Organization Admin validation
            if ($user->isOrgAdmin()) {
                $hasPM = $this->filled('project_manager_id');
                $role = $this->input('role');

                // Role-based validation for Org Admin
                if ($role === 'member') {
                    // Member role requires project_manager_id
                    if (! $hasPM) {
                        $validator->errors()->add(
                            'project_manager_id',
                            'The project_manager_id field is required when inviting a Member.'
                        );
                    }
                } elseif ($role === 'project_manager') {
                    // Project Manager role should NOT have project_manager_id
                    if ($hasPM) {
                        $validator->errors()->add(
                            'project_manager_id',
                            'The project_manager_id field is not allowed when inviting a Project Manager.'
                        );
                    }
                }
            }
        });
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
        ];
    }
}
