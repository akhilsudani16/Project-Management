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

        // Super Admin - must provide role and organization_id
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
            $rules['organization_id'] = ['required', 'uuid', 'exists:organizations,id'];
            $rules['project_id'] = ['nullable', 'uuid', 'exists:projects,id'];
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
            $rules['project_id'] = ['nullable', 'uuid', 'exists:projects,id'];
        }
        // Project Manager - can only invite Member
        elseif ($user->isProjectManager()) {
            // Check if PM has multiple projects
            $projectsCount = $user->projects()->count();
            if ($projectsCount > 1) {
                $rules['project_id'] = ['required', 'uuid', 'exists:projects,id'];
            } else {
                $rules['project_id'] = ['nullable', 'uuid', 'exists:projects,id'];
            }
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
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization does not exist.',
            'project_id.required' => 'Project is required when you manage multiple projects.',
            'project_id.exists' => 'Selected project does not exist.',
        ];
    }
}
