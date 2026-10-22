<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $fillable = ['user_id', 'especialidade_id', 'crm'];

    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class);
    }
}