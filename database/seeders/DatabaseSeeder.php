<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrador', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Empleado', 'email' => 'empleado@example.com', 'role' => 'empleado'],
            ['name' => 'Profesor', 'email' => 'profesor@example.com', 'role' => 'profesor'],
            ['name' => 'Estudiante', 'email' => 'alumno@example.com', 'role' => 'estudiante'],
            ['name' => 'Estudiante', 'email' => 'estudiante@example.com', 'role' => 'estudiante'],
            ['name' => 'Jugador', 'email' => 'jugador@example.com', 'role' => 'jugador'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                ]
            );
        }

        $this->call(ProductCatalogSeeder::class);
    }
}
