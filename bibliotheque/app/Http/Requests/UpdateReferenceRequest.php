<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReferenceRequest extends FormRequest
{
    // Autorise la mise à jour d'une référence
    public function authorize(): bool
    {
        return true;
    }

    // Validation des champs optionnels d'une référence (ISBN unique, type document, statut)
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('references')->ignore($this->reference)],
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'nullable|in:fr,en,autre',
            'document_type_id' => 'nullable|exists:document_types,id',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'status' => 'nullable|in:draft,published,archived',
            'pages' => 'nullable|integer|min:1',
            'allow_download' => 'boolean',
        ];
    }
}