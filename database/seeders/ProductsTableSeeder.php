<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\StorageLocation;
use App\Models\StockMovement;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $category = Category::first();
        $brand = Brand::first();
        $supplier = Supplier::first();
        $location = StorageLocation::first();

        $products = [
            [
                'code' => 'P-0001',
                'name' => 'Kampas Rem Depan',
                'brand_id' => $brand->id ?? null,
                'category_id' => $category->id ?? null,
                'supplier_id' => $supplier->id ?? null,
                'storage_location_id' => $location->id ?? null,
                'stock' => 30,
                'purchase_price' => 30000,
                'sale_price' => 45000,
                'ordering_cost' => 50000,
                'holding_cost' => 3000,
                'annual_demand' => 360,
                'is_active' => true,
            ],
            [
                'code' => 'P-0002',
                'name' => 'Busi Motor Tipe X',
                'brand_id' => $brand->id ?? null,
                'category_id' => $category->id ?? null,
                'supplier_id' => $supplier->id ?? null,
                'storage_location_id' => $location->id ?? null,
                'stock' => 50,
                'purchase_price' => 15000,
                'sale_price' => 25000,
                'ordering_cost' => 30000,
                'holding_cost' => 1500,
                'annual_demand' => 600,
                'is_active' => true,
            ],
        ];

        foreach ($products as $p) {
            $prod = Product::updateOrCreate(['code' => $p['code']], $p);

            // create initial stock movement record (in) jika belum ada
            if ($prod->stock > 0) {
                $exists = StockMovement::where('product_id', $prod->id)
                                        ->where('reference_type','seed')->exists();
                if (! $exists) {
                    StockMovement::create([
                        'product_id' => $prod->id,
                        'type' => 'in',
                        'quantity' => $prod->stock,
                        'reference_type' => 'seed',
                        'reference_id' => null,
                        'user_id' => 1, // asumsi owner/admin pertama punya ID 1; jika beda, sesuaikan
                    ]);
                }
            }
        }

        // contoh generate produk tambahan (opsional)
        $faker = \Faker\Factory::create('id_ID');
        for ($i=3; $i<=10; $i++) {
            $code = 'P-'.str_pad($i,4,'0',STR_PAD_LEFT);
            Product::updateOrCreate(
                ['code'=>$code],
                [
                    'name' => $faker->word . ' ' . $i,
                    'brand_id' => $brand->id ?? null,
                    'category_id' => $category->id ?? null,
                    'supplier_id' => $supplier->id ?? null,
                    'storage_location_id' => $location->id ?? null,
                    'stock' => $qty = rand(5,80),
                    'purchase_price' => $pp = rand(10000,200000),
                    'sale_price' => round($pp * 1.35),
                    'ordering_cost' => rand(20000,60000),
                    'holding_cost' => round($pp * 0.05),
                    'annual_demand' => rand(50,1000),
                    'is_active' => true,
                ]
            );
        }
    }
}
