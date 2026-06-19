<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'action' => 'required|string|max:255',
            'target_table' => 'required|string|max:100',
            'target_id' => 'nullable|integer',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ];
    }
}