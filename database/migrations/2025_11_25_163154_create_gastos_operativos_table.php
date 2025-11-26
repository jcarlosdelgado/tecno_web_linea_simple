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
        Schema::create('gastos_operativos', function (Blueprint $table) {
            $table->id('id_gasto_operativo');
            $table->string('categoria', 100); // Ej: Transporte, Alimentación, Servicios, Mantenimiento, etc.
            $table->string('descripcion', 255);
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('comprobante', 255)->nullable(); // Ruta al archivo del comprobante
            $table->unsignedBigInteger('registrado_por'); // Usuario que registró
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_operativos');
    }
};
