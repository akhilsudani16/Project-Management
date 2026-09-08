<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization');
        $roleToInvite = $this->input('role', 'member');

        return $this->user()->can('inviteMembers', [$organization, $roleToInvite]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['organization_admin', 'project_manager', 'member'])],
            'name' => ['required_without:existing_user', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => __('user.email')]),
            'email.email' => __('validation.email', ['attribute' => __('user.email')]),
            'role.required' => __('validation.required', ['attribute' => __('user.role')]),
            'role.in' => __('validation.in', ['attribute' => __('user.role')]),
            'name.required_without' => __('validation.required', ['attribute' => __('user.name')]),
        ];
    }
}
