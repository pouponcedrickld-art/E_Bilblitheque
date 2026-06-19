<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($this->category)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($this->category)],
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}