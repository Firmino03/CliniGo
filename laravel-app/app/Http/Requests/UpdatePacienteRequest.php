<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePacienteRequest extends FormRequest
{
        public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'            => ['sometimes', 'required', 'string', 'max:255'],
            'cpf'             => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/',
                 Rule::unique('pacientes', 'cpf')->ignore($this->route('paciente'))],
            'data_nascimento' => ['sometimes', 'required', 'date', 'before:today'],
            'telefone'        => ['sometimes', 'required', 'string', 'max:15'],
        ];
    }
}
