<?php

namespace Database\Seeders;

use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // usuario fijo
    Usuario::factory()->create([
        'nombre' => 'Admin',
        'email' => 'admin@foro.com',
        'password' => bcrypt('admin123'),
    ]);

    // usuarios aleatorios
    Usuario::factory(10)->create();
}
}
