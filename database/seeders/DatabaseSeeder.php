<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        $user = User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Crear dirección para el usuario
        Address::create([
            'user_id' => $user->id,
            'street' => 'Calle Falsa 123',
            'city' => 'Buenos Aires',
            'state' => 'CABA',
            'zip' => '1234',
            'country' => 'Argentina',
            'type' => 'delivery',
        ]);

        // Crear productos de ejemplo
        Product::create([
            'name' => 'Smartphone Samsung Galaxy',
            'description' => 'Teléfono inteligente de última generación',
            'price' => 89999.99,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Laptop Dell Inspiron',
            'description' => 'Computadora portátil para uso profesional',
            'price' => 125999.99,
            'stock' => 25,
        ]);

        Product::create([
            'name' => 'Auriculares Bluetooth Sony',
            'description' => 'Auriculares inalámbricos con cancelación de ruido',
            'price' => 15999.99,
            'stock' => 100,
        ]);

        Product::create([
            'name' => 'Smart TV LG 55"',
            'description' => 'Televisor inteligente 4K Ultra HD',
            'price' => 75999.99,
            'stock' => 30,
        ]);

        Product::create([
            'name' => 'Mouse Gaming Logitech',
            'description' => 'Mouse gaming con sensor de alta precisión',
            'price' => 8999.99,
            'stock' => 75,
        ]);
    }
}
