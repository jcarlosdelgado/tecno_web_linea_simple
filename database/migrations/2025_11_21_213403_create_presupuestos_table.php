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
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id('id_presupuesto');
            $table->foreignId('id_trabajo')->constrained('trabajos', 'id_trabajo');
            $table->enum('estado', ['ENVIADO', 'APROBADO', 'RECHAZADO'])->default('ENVIADO');
            $table->decimal('total_materiales', 10, 2)->default(0);
            $table->decimal('total_mano_obra', 10, 2)->default(0);
            $table->decimal('total_otros', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->dateTime('fecha_respuesta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
