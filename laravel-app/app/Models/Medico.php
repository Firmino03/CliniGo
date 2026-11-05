<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medico extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'especialidade_id', 'crm'];

    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class);
    }
}