<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Cartera Mitsubishi Montero', 'description' => 'Juego de 3 carteras en fibra de vidrio para Mitsubishi Montero. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 3, 'base_price' => 450000],
            ['name' => 'Cartera Mitsubishi Hard Top', 'description' => 'Juego de 2 carteras en fibra de vidrio para Mitsubishi Hard Top. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 1000000],
            ['name' => 'Cartera Mazda 323 Coupé', 'description' => 'Juego de 2 carteras en fibra de vidrio para Mazda 323 Coupé. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Mazda 323 4 Puertas', 'description' => 'Juego de 4 carteras en fibra de vidrio para Mazda 323 4 Puertas. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 395000],
            ['name' => 'Cartera Mazda B2000', 'description' => 'Juego de 2 carteras en fibra de vidrio para Mazda B2000. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Mazda B2000 Doble Cabina', 'description' => 'Juego de 4 carteras en fibra de vidrio para Mazda B2000 Doble Cabina. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 420000],
            ['name' => 'Cartera Mazda Turbo T45', 'description' => 'Juego de 2 carteras en fibra de vidrio para Mazda Turbo T45. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Mazda Allegro', 'description' => 'Juego de 4 carteras en fibra de vidrio para Mazda Allegro. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 650000],
            ['name' => 'Cartera Mazda Asahi', 'description' => 'Juego de 4 carteras en fibra de vidrio para Mazda Asahi. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 495000],
            ['name' => 'Cartera Chevrolet Chevette', 'description' => 'Juego de 4 carteras en fibra de vidrio para Chevrolet Chevette. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 550000],
            ['name' => 'Cartera Chevrolet C10', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet C10. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet C30', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet C30. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet C70', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet C70. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet Sidewalk', 'description' => 'Juego de 4 carteras en fibra de vidrio para Chevrolet Sidewalk. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 800000],
            ['name' => 'Cartera Chevrolet Sprint', 'description' => 'Juego de 5 carteras en fibra de vidrio para Chevrolet Sprint. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 5, 'base_price' => 395000],
            ['name' => 'Cartera Chevrolet Swift 1.3', 'description' => 'Juego de 8 carteras en fibra de vidrio para Chevrolet Swift 1.3. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 8, 'base_price' => 450000],
            ['name' => 'Cartera Chevrolet Vitara 3 Puertas', 'description' => 'Juego de 3 carteras en fibra de vidrio para Chevrolet Vitara 3 Puertas. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 3, 'base_price' => 460000],
            ['name' => 'Cartera Chevrolet NPR', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet NPR. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet Swift 1.0', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet Swift 1.0. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 320000],
            ['name' => 'Cartera Chevrolet Samurai', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet Samurai. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet LUV 1600', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet LUV 1600. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet LUV 2300', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet LUV 2300. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Chevrolet LUV 2300 Doble Cabina', 'description' => 'Juego de 4 carteras en fibra de vidrio para Chevrolet LUV 2300 Doble Cabina. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 420000],
            ['name' => 'Cartera Hafei Ruiyi', 'description' => 'Juego de 2 carteras en fibra de vidrio para Hafei Ruiyi. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Toyota 4.5', 'description' => 'Juego de 2 carteras en fibra de vidrio para Toyota 4.5. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 330000],
            ['name' => 'Cartera Toyota Hilux Cabina Sencilla', 'description' => 'Juego de 2 carteras en fibra de vidrio para Toyota Hilux Cabina Sencilla. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 380000],
            ['name' => 'Cartera Toyota Hilux Doble Cabina Mod. 98', 'description' => 'Juego de 4 carteras en fibra de vidrio para Toyota Hilux Doble Cabina modelo 98. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 550000],
            ['name' => 'Cartera Dodge 100', 'description' => 'Juego de 2 carteras en fibra de vidrio para Dodge 100. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Dodge 300', 'description' => 'Juego de 2 carteras en fibra de vidrio para Chevrolet Dodge 300. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Dodge 600', 'description' => 'Juego de 2 carteras en fibra de vidrio para Dodge 600. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Ford Festiva', 'description' => 'Juego de 4 carteras en fibra de vidrio para Ford Festiva. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 600000],
            ['name' => 'Cartera Suzuki 410', 'description' => 'Juego de 2 carteras en fibra de vidrio para Suzuki 410. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Suzuki LJ80', 'description' => 'Juego de 2 carteras en fibra de vidrio para Suzuki LJ80. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Hyundai Accent', 'description' => 'Juego de 4 carteras en fibra de vidrio para Hyundai Accent. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 420000],
            ['name' => 'Cartera Daewoo Cielo', 'description' => 'Juego de 4 carteras en fibra de vidrio para Daewoo Cielo. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 420000],
            ['name' => 'Cartera Daihatsu Delta', 'description' => 'Juego de 2 carteras en fibra de vidrio para Daihatsu Delta. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Cartera Changhe Freedom', 'description' => 'Juego de 2 carteras en fibra de vidrio para Changhe Freedom. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 2, 'base_price' => 300000],
            ['name' => 'Portacomandos Chevrolet Optra', 'description' => 'Juego de 4 portacomandos en fibra de vidrio para Chevrolet Optra. Fabricación propia en Cali. Envío contraentrega a todo Colombia.', 'pieces' => 4, 'base_price' => 230000],
        ];

        foreach ($products as $product) {
            Product::create([
                ...$product,
                'avg_production_days' => 7,
                'active' => true,
            ]);
        }
    }
}
