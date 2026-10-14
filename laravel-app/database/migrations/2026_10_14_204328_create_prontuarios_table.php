<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('prontuarios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('consulta_id')->unique()->constrained('consultas')->restrictOnDelete();        $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
        $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete() ;
        $table->text('anotacoes');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prontuarios');
    }
};
