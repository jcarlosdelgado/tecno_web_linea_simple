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
        Schema::create('pagos_pasarela', function (Blueprint $table) {
            $table->id('id_pasarela');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->string('transaction_id', 100)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->string('estado', 50)->nullable();
            $table->text('respuesta_json')->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_pasarela');
    }
};
