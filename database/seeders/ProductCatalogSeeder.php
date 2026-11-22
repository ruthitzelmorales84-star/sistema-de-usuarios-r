<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Electrónica',
            'Papelería',
            'Hogar',
            'Alimentos',
        ])->mapWithKeys(function ($name) {
            $category = Category::firstOrCreate(['name' => $name]);
            return [$name => $category->id];
        });

        $products = [
            [
                'category_id' => $categories['Papelería'],
                'name' => 'Cuaderno Profesional 100 Hojas',
                'description' => 'Cuaderno cosido cuadro chico con pasta dura.',
                'price' => 45.00,
                'stock' => 30,
                'status' => 'disponible',
            ],
            [
                'category_id' => $categories['Electrónica'],
                'name' => 'Mouse Inalámbrico Óptico',
                'description' => 'Mouse ergonómico de 2.4 GHz con receptor USB.',
                'price' => 199.50,
                'stock' => 0,
                'status' => 'agotado',
            ],
            [
                'category_id' => $categories['Hogar'],
                'name' => 'Lámpara de Escritorio LED',
                'description' => 'Lámpara articulada con luz cálida y fría.',
                'price' => 320.00,
                'stock' => 12,
                'status' => 'disponible',
            ],
            [
                'category_id' => $categories['Alimentos'],
                'name' => 'Café Molido Tostado Medio 500g',
                'description' => 'Bolsa de café molido ideal para cafetera o prensa.',
                'price' => 135.00,
                'stock' => 18,
                'status' => 'disponible',
            ],
        ];

        foreach ($products as $item) {
            Product::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}