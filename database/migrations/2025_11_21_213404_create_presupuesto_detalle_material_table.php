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
        Schema::create('presupuesto_detalle_material', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_presupuesto')->constrained('presupuestos', 'id_presupuesto');
            $table->foreignId('id_material')->constrained('materiales', 'id_material');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_detalle_material');
    }
};
