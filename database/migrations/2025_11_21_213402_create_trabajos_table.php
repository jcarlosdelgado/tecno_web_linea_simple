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
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id('id_trabajo');
            $table->foreignId('id_cliente')->constrained('usuarios', 'id_usuario');
            $table->string('titulo', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado', [
                'SOLICITADO',
                'PRESUPUESTADO',
                'EN_PRODUCCION',
                'FINALIZADO',
                'CANCELADO'
            ])->default('SOLICITADO');
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos');
    }
};
