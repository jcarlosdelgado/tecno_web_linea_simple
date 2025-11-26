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
        Schema::table('pagos_pasarela', function (Blueprint $table) {
            // Agregar columna para vincular con cuotas específicas (pagos a crédito)
            $table->foreignId('cuota_id')
                ->nullable()
                ->after('id_pago')
                ->constrained('cuotas_pago', 'id_cuota')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_pasarela', function (Blueprint $table) {
            $table->dropForeign(['cuota_id']);
            $table->dropColumn('cuota_id');
        });
    }
};
