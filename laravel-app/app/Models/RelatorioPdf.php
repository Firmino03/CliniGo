<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioPdf extends Model
{
    protected $table = 'relatorios_pdf';

    public $timestamps = false;

    protected $fillable = ['user_id', 'tipo', 'filtros', 'arquivo_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}