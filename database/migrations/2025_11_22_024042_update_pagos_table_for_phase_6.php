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
        Schema::table('pagos', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('pagos', 'tipo_pago')) {
                $table->enum('tipo_pago', ['CONTADO', 'CREDITO'])->default('CONTADO')->after('monto');
            }
            if (!Schema::hasColumn('pagos', 'numero_cuotas')) {
                $table->integer('numero_cuotas')->nullable()->after('tipo_pago');
            }
            if (!Schema::hasColumn('pagos', 'monto_cuota')) {
                $table->decimal('monto_cuota', 10, 2)->nullable()->after('numero_cuotas');
            }
            if (!Schema::hasColumn('pagos', 'estado')) {
                $table->enum('estado', ['PENDIENTE', 'PAGADO', 'FALLIDO', 'EN_REVISION'])->default('PENDIENTE')->after('fecha');
            }
            if (!Schema::hasColumn('pagos', 'comprobante_url')) {
                $table->string('comprobante_url')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('pagos', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('comprobante_url');
            }
            
            // Update 'metodo' column to include new options if needed, or add a new 'metodo_pago' column
            // Since 'metodo' already exists as enum, we might need to modify it. 
            // For simplicity and safety with SQLite/Postgres enum limitations in Laravel migrations, 
            // we will add a new column 'metodo_pago' and deprecate 'metodo' or just use 'metodo' if it allows string.
            // Let's assume we can just add a new string column for flexibility or update the enum if possible.
            // Here we will try to modify the existing column if possible, or add a new one.
            // To be safe across DBs, let's add 'metodo_detalle' for specific gateway info.
            
            // Actually, let's just drop the constraint and recreate the column if it's an enum, or change it to string.
            // But to avoid data loss complexity, let's just add 'metodo_pago' as string to be flexible.
            if (!Schema::hasColumn('pagos', 'metodo_pago')) {
                $table->string('metodo_pago')->default('EFECTIVO')->after('metodo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['tipo_pago', 'numero_cuotas', 'monto_cuota', 'estado', 'comprobante_url', 'transaction_id', 'metodo_pago']);
        });
    }
};
