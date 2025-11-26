<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar el CHECK constraint de rol en usuarios para incluir TRABAJADOR
        DB::statement("ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_rol_check");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT usuarios_rol_check CHECK (rol IN ('PROPIETARIO', 'CLIENTE', 'TRABAJADOR'))");

        // 2. Agregar nuevos campos a la tabla trabajos
        Schema::table('trabajos', function (Blueprint $table) {
            // Medidas (JSON: {ancho, alto, profundidad})
            $table->jsonb('medidas')->nullable()->after('imagenes_referencia');
            
            // Cantidad de unidades
            $table->integer('cantidad')->nullable()->after('medidas');
            
            // Colores solicitados
            $table->string('colores', 255)->nullable()->after('cantidad');
            
            // Fecha estimada de entrega solicitada por el cliente
            $table->date('fecha_estimada')->nullable()->after('colores');
            
            // Trabajador asignado al trabajo
            $table->bigInteger('id_trabajador')->nullable()->after('id_servicio');
            $table->foreign('id_trabajador')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar campos agregados a trabajos
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropForeign(['id_trabajador']);
            $table->dropColumn(['medidas', 'cantidad', 'colores', 'fecha_estimada', 'id_trabajador']);
        });

        // Restaurar el CHECK constraint original
        DB::statement("ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_rol_check");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT usuarios_rol_check CHECK (rol IN ('PROPIETARIO', 'CLIENTE'))");
    }
};
