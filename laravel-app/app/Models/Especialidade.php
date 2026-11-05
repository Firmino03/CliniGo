<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Especialidade;

class Especialidade extends Model
{
    protected $fillable = ['nome'];

    public function medicos()
    {
        return $this->hasMany(Medico::class);
    }
}
