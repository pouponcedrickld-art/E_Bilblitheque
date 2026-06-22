<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreViewRequest extends FormRequest
{
    // Autorise tout le monde à enregistrer une vue
    public function authorize(): bool
    {
        return true;
    }

    // La référence ciblée doit exister
    public function rules(): array
    {
        return [
            'reference_id' => 'required|exists:references,id',
        ];
    }
}