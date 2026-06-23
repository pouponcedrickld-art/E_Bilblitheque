<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepositRequestRequest extends FormRequest
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
            'description' => 'nullable|string',
            'isbn' => 'nullable|string|unique:deposit_requests',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'in:fr,en,autre',
            'document_type' => 'in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'pages' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|max:5120',
            'proposed_file' => 'nullable|file|mimes:pdf,doc,docx,odt,txt|max:10240',
        ];
    }
}