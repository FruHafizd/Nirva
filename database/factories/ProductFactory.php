<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static array $units = ['pcs', 'kg', 'liter', 'pack', 'botol', 'sachet', 'dus', 'bungkus'];

    /**
     * Nama produk realistis ala toko/minimarket Indonesia.
     * Menggunakan kombinasi brand + varian untuk menghasilkan ribuan nama unik.
     */
    private static array $brands = [
        'Indomie', 'Sedaap', 'Sarimi', 'Supermi', 'ABC', 'Indofood',
        'Kapal Api', 'Torabika', 'Good Day', 'Nescafe', 'Luwak',
        'Aqua', 'Le Minerale', 'Pristine', 'Cleo', 'Club',
        'Chitato', 'Lays', 'Qtela', 'Taro', 'Potabee', 'Pringles',
        'Ultra Milk', 'Frisian Flag', 'Bear Brand', 'Greenfields',
        'Sosro', 'Frestea', 'Teh Pucuk', 'Ichi Ocha', 'Teh Kotak',
        'Pocari Sweat', 'Mizone', 'You C 1000', 'Hydro Coco',
        'SilverQueen', 'Cadbury', 'KitKat', 'Beng Beng', 'TOP',
        'Roma', 'Biskuat', 'Oreo', 'Good Time', 'Monde',
        'Sunlight', 'Rinso', 'Attack', 'So Klin', 'Molto',
        'Lifebuoy', 'Lux', 'Dove', 'Dettol', 'Biore',
        'Pepsodent', 'Close Up', 'Sensodyne', 'Formula', 'Ciptadent',
        'Sunsilk', 'Pantene', 'Clear', 'Head Shoulders', 'Rejoice',
        'Gulaku', 'Bimoli', 'Sania', 'Filma', 'Rose Brand',
        'Sasa', 'Masako', 'Royco', 'Ajinomoto', 'Kokita',
    ];

    private static array $variants = [
        'Original', 'Spesial', 'Extra Pedas', 'Rasa Ayam', 'Rasa Sapi',
        'Goreng', 'Kuah', 'Jumbo', 'Mini', 'Premium',
        'Reguler', 'Sachet', 'Botol Besar', 'Botol Kecil', 'Kaleng',
        'Double Pack', 'Family Pack', 'Ekonomis', 'Refill', 'Pouch',
        'Rasa Coklat', 'Rasa Vanila', 'Rasa Strawberry', 'Rasa Melon', 'Rasa Jeruk',
        'Rasa Keju', 'Rasa BBQ', 'Rasa Balado', 'Rumput Laut', 'Rasa Rendang',
        '100ml', '250ml', '500ml', '600ml', '1L', '1.5L', '2L',
        '50g', '80g', '100g', '150g', '200g', '250g', '500g', '1kg', '2kg', '5kg',
    ];

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 1000, 150000);
        $brand = fake()->randomElement(self::$brands);
        $variant = fake()->randomElement(self::$variants);

        return [
            'category_id' => Category::factory(),
            'name'        => fake()->unique()->numerify($brand . ' ' . $variant . ' ##'),
            'sku'         => strtoupper(fake()->unique()->bothify('PRD-####-??')),
            'description' => fake()->sentence(),
            'price'       => $price,
            'cost_price'  => round($price * fake()->randomFloat(2, 0.5, 0.85), 2),
            'stock'       => fake()->numberBetween(0, 500),
            'unit'        => fake()->randomElement(self::$units),
            'barcode'     => fake()->boolean(70) ? fake()->unique()->ean13() : null,
            'is_active'   => fake()->boolean(90), // 90% aktif
            'image_url'   => null,
        ];
    }

    /**
     * State: produk habis stok.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * State: produk non-aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Assign ke kategori tertentu.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
