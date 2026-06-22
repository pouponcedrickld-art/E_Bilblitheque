<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublisherRequest extends FormRequest
{
    // Autorise la mise à jour d'un éditeur
    public function authorize(): bool
    {
        return true;
    }

    // Validation des champs optionnels d'un éditeur (nom unique, site valide)
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255', Rule::unique('publishers')->ignore($this->publisher)],
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
        ];
    }
}