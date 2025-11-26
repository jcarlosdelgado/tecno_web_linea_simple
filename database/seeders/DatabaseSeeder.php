<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Usuarios base
            UserSeeder::class,
            
            // Sistema de permisos y roles
            PermisosSeeder::class,
            RolesEjemploSeeder::class,
            
            // Datos del negocio
            ServicioSeeder::class,
            ProveedorSeeder::class,
            MaterialSeeder::class,
            TrabajoSeeder::class,
            PagoSeeder::class,
        ]);
    }
}
