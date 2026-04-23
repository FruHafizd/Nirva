<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua kategori berdasarkan slug (key = slug, value = id)
        $categories = Category::pluck('id', 'slug');

        // ==============================
        // PRODUK MANUAL (Data realistis)
        // ==============================
        $products = [
            // ========== MAKANAN RINGAN ==========
            [
                'category_id' => $categories['makanan-ringan'],
                'name' => 'Chitato Sapi Panggang 68g',
                'sku' => 'SNK-001',
                'description' => 'Keripik kentang rasa sapi panggang',
                'price' => 10500,
                'cost_price' => 8400,
                'stock' => 48,
                'unit' => 'pcs',
                'barcode' => '8886008101053',
            ],
            [
                'category_id' => $categories['makanan-ringan'],
                'name' => 'Taro Net Seaweed 36g',
                'sku' => 'SNK-002',
                'description' => 'Snack jaring rasa rumput laut',
                'price' => 5500,
                'cost_price' => 4200,
                'stock' => 72,
                'unit' => 'pcs',
                'barcode' => '8886008101070',
            ],
            [
                'category_id' => $categories['makanan-ringan'],
                'name' => 'Qtela Tempe Original 55g',
                'sku' => 'SNK-003',
                'description' => 'Keripik tempe original',
                'price' => 8500,
                'cost_price' => 6800,
                'stock' => 36,
                'unit' => 'pcs',
                'barcode' => '8886008101087',
            ],
            [
                'category_id' => $categories['makanan-ringan'],
                'name' => 'Potabee Ayam Bakar 68g',
                'sku' => 'SNK-004',
                'description' => 'Keripik kentang rasa ayam bakar',
                'price' => 10500,
                'cost_price' => 8400,
                'stock' => 30,
                'unit' => 'pcs',
                'barcode' => '8886008101094',
            ],
            [
                'category_id' => $categories['makanan-ringan'],
                'name' => 'Lays Rumput Laut 68g',
                'sku' => 'SNK-005',
                'description' => 'Keripik kentang rasa rumput laut',
                'price' => 11000,
                'cost_price' => 8800,
                'stock' => 42,
                'unit' => 'pcs',
                'barcode' => '8886008101100',
            ],

            // ========== MINUMAN ==========
            [
                'category_id' => $categories['minuman'],
                'name' => 'Aqua Botol 600ml',
                'sku' => 'MNM-001',
                'description' => 'Air mineral dalam kemasan botol',
                'price' => 4000,
                'cost_price' => 2800,
                'stock' => 200,
                'unit' => 'botol',
                'barcode' => '8886008102001',
            ],
            [
                'category_id' => $categories['minuman'],
                'name' => 'Teh Botol Sosro 450ml',
                'sku' => 'MNM-002',
                'description' => 'Teh manis dalam kemasan botol PET',
                'price' => 5500,
                'cost_price' => 4000,
                'stock' => 120,
                'unit' => 'botol',
                'barcode' => '8886008102018',
            ],
            [
                'category_id' => $categories['minuman'],
                'name' => 'Pocari Sweat 500ml',
                'sku' => 'MNM-003',
                'description' => 'Minuman isotonik',
                'price' => 8000,
                'cost_price' => 6200,
                'stock' => 80,
                'unit' => 'botol',
                'barcode' => '8886008102025',
            ],
            [
                'category_id' => $categories['minuman'],
                'name' => 'Mizone Cherry Blossom 500ml',
                'sku' => 'MNM-004',
                'description' => 'Minuman isotonik rasa cherry',
                'price' => 6000,
                'cost_price' => 4500,
                'stock' => 60,
                'unit' => 'botol',
                'barcode' => '8886008102032',
            ],
            [
                'category_id' => $categories['minuman'],
                'name' => 'Bear Brand 189ml',
                'sku' => 'MNM-005',
                'description' => 'Susu steril',
                'price' => 10500,
                'cost_price' => 8500,
                'stock' => 48,
                'unit' => 'kaleng',
                'barcode' => '8886008102049',
            ],

            // ========== MIE INSTAN ==========
            [
                'category_id' => $categories['mie-instan'],
                'name' => 'Indomie Goreng Spesial',
                'sku' => 'MIE-001',
                'description' => 'Mie instan goreng rasa original',
                'price' => 3200,
                'cost_price' => 2600,
                'stock' => 320,
                'unit' => 'pcs',
                'barcode' => '8886008103001',
            ],
            [
                'category_id' => $categories['mie-instan'],
                'name' => 'Sedaap Mie Goreng',
                'sku' => 'MIE-002',
                'description' => 'Mie instan goreng dengan kriuk',
                'price' => 3100,
                'cost_price' => 2500,
                'stock' => 280,
                'unit' => 'pcs',
                'barcode' => '8886008103018',
            ],
            [
                'category_id' => $categories['mie-instan'],
                'name' => 'Indomie Rasa Ayam Bawang',
                'sku' => 'MIE-003',
                'description' => 'Mie instan kuah rasa ayam bawang',
                'price' => 3000,
                'cost_price' => 2400,
                'stock' => 240,
                'unit' => 'pcs',
                'barcode' => '8886008103025',
            ],

            // ========== BAHAN POKOK ==========
            [
                'category_id' => $categories['bahan-pokok'],
                'name' => 'Beras Pandan Wangi 5kg',
                'sku' => 'BPK-001',
                'description' => 'Beras kualitas premium',
                'price' => 75000,
                'cost_price' => 65000,
                'stock' => 20,
                'unit' => 'pack',
                'barcode' => '8886008104001',
            ],
            [
                'category_id' => $categories['bahan-pokok'],
                'name' => 'Minyak Goreng Bimoli 2L',
                'sku' => 'BPK-002',
                'description' => 'Minyak goreng kelapa sawit',
                'price' => 36000,
                'cost_price' => 32000,
                'stock' => 40,
                'unit' => 'pouch',
                'barcode' => '8886008104018',
            ],
            [
                'category_id' => $categories['bahan-pokok'],
                'name' => 'Gula Pasir Gulaku 1kg',
                'sku' => 'BPK-003',
                'description' => 'Gula pasir putih premium',
                'price' => 17500,
                'cost_price' => 15500,
                'stock' => 50,
                'unit' => 'pcs',
                'barcode' => '8886008104025',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // ==============================
        // PRODUK FACTORY (Data besar untuk tes performa pencarian)
        // Generate 500 produk per kategori = total ~6000 produk
        // ==============================
        $allCategories = Category::all();

        foreach ($allCategories as $category) {
            Product::factory()
                ->count(500)
                ->forCategory($category)
                ->create();
        }
    }
}
