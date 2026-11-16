<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
        public function authorize(): bool
    {
        return true; // as permissões por perfil entram depois, com Policies
    }

    public function rules(): array
    {
        return [
            'nome'            => ['required', 'string', 'max:255'],
            'cpf'             => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/', 'unique:pacientes,cpf'],
            'data_nascimento' => ['required', 'date', 'before:today'],
            'telefone'        => ['required', 'string', 'max:15'],
        ];
    }
}
