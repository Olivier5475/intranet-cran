<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveGroupeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un admin peut gérer les groupes
        return auth()->user()->role === 'superadmin';
    }

    public function rules(): array
    {
        return [
            'initials' => ['required', 'string', 'max:255'],
            'name'     => ['required', 'string', 'max:255'],
            'color'    => ['required', 'string', 'max:255'],
        ];
    }
}
