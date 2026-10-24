<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cpf',
        'data_nascimento',
        'telefone'
    ];

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
