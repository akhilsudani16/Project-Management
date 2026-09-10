<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use App\Traits\ConvertsModelNames;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    use ConvertsModelNames;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],

            // Optional: Attach tag to resource during creation
            'taggable_type' => [
                'nullable',
                'string',
                Rule::in(['Project', 'Task']),
                'required_with:taggable_id',
            ],
            'taggable_id' => [
                'nullable',
                'uuid',
                'required_with:taggable_type',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'taggable_type.required_with' => 'Taggable type is required when taggable ID is provided.',
            'taggable_id.required_with' => 'Taggable ID is required when taggable type is provided.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert short model name to fully qualified class name for processing
        if ($this->has('taggable_type')) {
            $fullClassName = $this->getFullModelClass($this->input('taggable_type'));

            if ($fullClassName) {
                $this->merge([
                    'taggable_type_full' => $fullClassName,
                ]);
            }
        }
    }
}
