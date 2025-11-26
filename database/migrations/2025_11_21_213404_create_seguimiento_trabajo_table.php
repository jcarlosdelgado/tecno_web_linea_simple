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
        Schema::create('seguimiento_trabajo', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->foreignId('id_trabajo')->constrained('trabajos', 'id_trabajo');
            $table->text('descripcion')->nullable();
            $table->integer('porcentaje_avance')->default(0); // Add check constraint in raw SQL if needed, or just validate in app
            $table->string('evidencias_url', 255)->nullable();
            $table->dateTime('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_trabajo');
    }
};
