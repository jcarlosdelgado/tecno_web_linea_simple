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
        // First, remove id_proveedor from materiales if it exists
        Schema::table('materiales', function (Blueprint $table) {
            if (Schema::hasColumn('materiales', 'id_proveedor')) {
                $table->dropForeign(['id_proveedor']);
                $table->dropColumn('id_proveedor');
            }
        });

        // Create the intermediate table
        Schema::create('material_proveedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_material');
            $table->unsignedBigInteger('id_proveedor');
            $table->decimal('precio_unitario', 10, 2);
            $table->boolean('es_principal')->default(false);
            $table->integer('tiempo_entrega_dias')->nullable();
            $table->text('notas')->nullable();
            
            // Custom timestamps to match project convention
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys
            $table->foreign('id_material')->references('id_material')->on('materiales')->onDelete('cascade');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores')->onDelete('cascade');

            // Unique constraint to prevent duplicates
            $table->unique(['id_material', 'id_proveedor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_proveedor');
    }
};
