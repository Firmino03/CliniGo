<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prontuario extends Model
{
    protected $fillable = ['consulta_id', 'paciente_id', 'medico_id', 'anotacoes'];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}