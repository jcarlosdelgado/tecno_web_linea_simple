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
        // Tabla de roles personalizados
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de permisos disponibles
        Schema::create('permisos', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->string('nombre')->unique(); // ej: 'ver_trabajos', 'editar_pagos'
            $table->string('descripcion');
            $table->string('modulo'); // ej: 'trabajos', 'pagos', 'inventario'
            $table->timestamps();
        });

        // Tabla pivote: roles_permisos
        Schema::create('roles_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rol')->constrained('roles', 'id_rol')->onDelete('cascade');
            $table->foreignId('id_permiso')->constrained('permisos', 'id_permiso')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['id_rol', 'id_permiso']);
        });

        // Agregar columna id_rol a la tabla usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('id_rol')->nullable()->after('rol')->constrained('roles', 'id_rol')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropColumn('id_rol');
        });
        
        Schema::dropIfExists('roles_permisos');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
