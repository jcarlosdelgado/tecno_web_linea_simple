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
        Schema::create('movimientos_material', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_material')->constrained('materiales', 'id_material');
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->foreignId('id_proveedor')->nullable()->constrained('proveedores', 'id_proveedor');
            // id_trabajo will be added later or nullable here if circular dependency is a concern, but usually fine in migration order if trabajos exists.
            // However, trabajos migration is later in the list. I should make it nullable and maybe add constraint later or just integer for now if I can't guarantee order, 
            // but since I'm editing files, I can assume standard execution order. 
            // Wait, 'trabajos' migration timestamp is 213402, same as this one. Alphabetically 'create_movimientos...' comes before 'create_trabajos...'.
            // So 'trabajos' table won't exist yet when this runs. I must use unsignedBigInteger and add foreign key in a separate migration or just rely on 'trabajos' running first if I rename it.
            // Better approach: Make it nullable unsignedBigInteger, and I'll add the constraint in the 'trabajos' migration or a separate one.
            // Actually, I'll just define the column here and maybe skip the FK constraint for now to avoid errors, or just assume the user will run migrate and I can fix order.
            // Let's just define the column.
            $table->unsignedBigInteger('id_trabajo')->nullable(); 
            $table->dateTime('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_material');
    }
};
