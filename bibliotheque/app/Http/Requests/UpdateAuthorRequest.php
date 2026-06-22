<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
{
    // Autorise la mise à jour d'un auteur
    public function authorize(): bool
    {
        return true;
    }

    // Validation des champs optionnels d'un auteur (date de décès après naissance)
    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
        ];
    }
}