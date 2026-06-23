<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|unique:references',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'in:fr,en,autre',
            'document_type_id' => 'nullable|exists:document_types,id',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'cover_image' => 'nullable|image|max:5120',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'pages' => 'nullable|integer|min:1',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'keyword_ids' => 'nullable|array',
            'keyword_ids.*' => 'exists:keywords,id',
            'is_featured' => 'boolean',
        ];
    }
}