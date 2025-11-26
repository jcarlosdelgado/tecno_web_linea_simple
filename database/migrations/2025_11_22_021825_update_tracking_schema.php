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
        // Add hours to seguimiento_trabajo
        Schema::table('seguimiento_trabajo', function (Blueprint $table) {
            if (!Schema::hasColumn('seguimiento_trabajo', 'horas_trabajadas')) {
                $table->decimal('horas_trabajadas', 8, 2)->default(0)->after('porcentaje_avance');
            }
        });

        // Create fotos_seguimiento table
        Schema::create('fotos_seguimiento', function (Blueprint $table) {
            $table->id('id_foto');
            $table->foreignId('id_seguimiento')->constrained('seguimiento_trabajo', 'id_seguimiento')->onDelete('cascade');
            $table->string('url_foto', 255);
            $table->dateTime('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fotos_seguimiento');
        
        Schema::table('seguimiento_trabajo', function (Blueprint $table) {
            if (Schema::hasColumn('seguimiento_trabajo', 'horas_trabajadas')) {
                $table->dropColumn('horas_trabajadas');
            }
        });
    }
};
