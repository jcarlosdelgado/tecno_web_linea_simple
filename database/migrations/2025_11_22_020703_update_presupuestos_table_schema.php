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
        // Drop the existing check constraint first
        DB::statement("ALTER TABLE presupuestos DROP CONSTRAINT IF EXISTS presupuestos_estado_check");
        
        // Update any existing 'ENVIADO' status to 'PENDIENTE'
        DB::table('presupuestos')->where('estado', 'ENVIADO')->update(['estado' => 'PENDIENTE']);
        
        Schema::table('presupuestos', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('presupuestos', 'monto_total')) {
                $table->decimal('monto_total', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('presupuestos', 'fecha_emision')) {
                $table->date('fecha_emision')->nullable();
            }
            
            if (!Schema::hasColumn('presupuestos', 'fecha_validez')) {
                $table->date('fecha_validez')->nullable();
            }
            
            if (!Schema::hasColumn('presupuestos', 'notas')) {
                $table->text('notas')->nullable();
            }
        });
        
        // Add the new check constraint
        DB::statement("ALTER TABLE presupuestos ADD CONSTRAINT presupuestos_estado_check CHECK (estado::text = ANY (ARRAY['PENDIENTE'::character varying, 'APROBADO'::character varying, 'RECHAZADO'::character varying]::text[]))");
    }


    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (Schema::hasColumn('presupuestos', 'monto_total')) {
                $table->dropColumn('monto_total');
            }
            if (Schema::hasColumn('presupuestos', 'fecha_emision')) {
                $table->dropColumn('fecha_emision');
            }
            if (Schema::hasColumn('presupuestos', 'fecha_validez')) {
                $table->dropColumn('fecha_validez');
            }
            if (Schema::hasColumn('presupuestos', 'notas')) {
                $table->dropColumn('notas');
            }
        });
    }
};
