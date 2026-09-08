<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Organization::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:organizations,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::enum(OrganizationStatus::class)],
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
            'name.required' => __('validation.required', ['attribute' => __('organization.name')]),
            'name.unique' => __('validation.unique', ['attribute' => __('organization.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('organization.name'), 'max' => 255]),
            'description.max' => __('validation.max.string', ['attribute' => __('organization.description'), 'max' => 1000]),
        ];
    }
}
