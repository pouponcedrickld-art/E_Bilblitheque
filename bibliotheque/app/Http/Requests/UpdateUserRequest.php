<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    // Autorise la mise à jour d'un utilisateur
    public function authorize(): bool
    {
        return true;
    }

    // Validation des champs optionnels (mot de passe actuel requis si nouveau mot de passe)
    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => ['nullable', Rule::in(['admin', 'responsable_rh', 'responsable_demande', 'user'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'current_password' => 'required_with:password|string',
        ];
    }
}