<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tag'));
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:50'],
        ];
    }
}
