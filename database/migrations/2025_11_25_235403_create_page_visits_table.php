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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('page_name'); // Nombre de la página (dashboard, trabajos, etc)
            $table->string('page_url'); // URL completa
            $table->integer('visit_count')->default(0); // Contador de visitas
            $table->date('visit_date'); // Fecha de la visita
            $table->timestamps();
            
            // Índice único para página + fecha
            $table->unique(['page_url', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
