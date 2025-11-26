<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Owner
        User::create([
            'nombre' => 'Carlos Propietario',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '70000001',
            'direccion' => 'Oficina Central',
            'rol' => 'PROPIETARIO',
        ]);

        // Create Clients
        User::create([
            'nombre' => 'Juan Cliente',
            'email' => 'cliente1@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '70000002',
            'direccion' => 'Av. Banzer 4to Anillo',
            'rol' => 'CLIENTE',
        ]);

        User::create([
            'nombre' => 'Maria Cliente',
            'email' => 'cliente2@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '70000003',
            'direccion' => 'Equipetrol Calle 5',
            'rol' => 'CLIENTE',
        ]);
    }
}
