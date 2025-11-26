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
        Schema::table('trabajos', function (Blueprint $table) {
            $table->foreignId('id_servicio')->nullable()->after('id_cliente')->constrained('servicios', 'id_servicio')->nullOnDelete();
            $table->json('imagenes_referencia')->nullable()->after('descripcion')->comment('Array de rutas de imágenes de referencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropForeign(['id_servicio']);
            $table->dropColumn(['id_servicio', 'imagenes_referencia']);
        });
    }
};
