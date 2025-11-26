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
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->decimal('mano_obra', 10, 2)->default(0)->after('monto_total');
            $table->decimal('otros_costos', 10, 2)->default(0)->after('mano_obra');
            $table->text('notas_adicionales')->nullable()->after('notas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropColumn(['mano_obra', 'otros_costos', 'notas_adicionales']);
        });
    }
};
