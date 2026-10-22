<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = ['nome', 'cpf', 'data_nascimento', 'telefone'];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }

    public function prontuarios()
    {
        return $this->hasMany(Prontuario::class);
    }
}