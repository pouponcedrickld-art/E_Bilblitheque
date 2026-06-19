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
            'isbn' => 'nullable|string|max:20|unique:references',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'nullable|in:fr,en,autre',
            'document_type' => 'required|in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'cover_image' => 'nullable|image|max:2048',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'pages' => 'nullable|integer|min:1',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
        ];
    }
}