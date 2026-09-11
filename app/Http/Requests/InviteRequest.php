<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
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
                'required',
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
                'required',
                'string',
                Rule::in([
                    UserRole::PROJECT_MANAGER->value,
                    UserRole::MEMBER->value,
                ]),
            ];
            // Org Admin can assign to PM
            $rules['project_manager_id'] = ['nullable', 'uuid', 'exists:users,id'];
        }
        // Project Manager - can only invite members
        elseif ($user->isProjectManager()) {
            // PM can only invite member role - no role selection needed
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

            // Verify org_admin_id is actually an Org Admin
            if ($this->filled('org_admin_id')) {
                $orgAdmin = User::find($this->input('org_admin_id'));
                if ($orgAdmin && ! $orgAdmin->isOrgAdmin()) {
                    $validator->errors()->add(
                        'org_admin_id',
                        'The selected user is not an Organization Admin.'
                    );
                }

                // Org Admin can only invite PM or Member
                $requestedRole = $this->input('role');
                if ($requestedRole && ! in_array($requestedRole, ['project_manager', 'member'], true)) {
                    $validator->errors()->add(
                        'role',
                        'When assigning to Org Admin, you can only invite Project Manager or Member.'
                    );
                }
            }

            // Verify project_manager_id is actually a PM
            if ($this->filled('project_manager_id')) {
                $pm = User::find($this->input('project_manager_id'));
                if ($pm && ! $pm->isProjectManager()) {
                    $validator->errors()->add(
                        'project_manager_id',
                        'The selected user is not a Project Manager.'
                    );
                }

                // PM can only have members under them
                $requestedRole = $this->input('role');
                if ($requestedRole && $requestedRole !== 'member') {
                    $validator->errors()->add(
                        'role',
                        'When assigning to Project Manager, you can only invite Member role.'
                    );
                }
            }

            // Super Admin must provide either org_admin_id OR project_manager_id
            if ($user->isSuperAdmin()) {
                if (! $this->filled('org_admin_id') && ! $this->filled('project_manager_id')) {
                    $validator->errors()->add(
                        'org_admin_id',
                        'You must provide either org_admin_id or project_manager_id.'
                    );
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
            'member_id.exists' => 'Selected Member does not exist.',
        ];
    }
}
