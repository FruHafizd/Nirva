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

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 1000, 150000);

        return [
            'category_id' => Category::factory(),
            'name'        => fake()->unique()->words(rand(2, 4), true),
            'sku'         => strtoupper(fake()->unique()->bothify('PRD-####-??')),
            'description' => fake()->sentence(),
            'price'       => $price,
            'cost_price'  => round($price * fake()->randomFloat(2, 0.5, 0.85), 2),
            'stock'       => fake()->numberBetween(0, 500),
            'unit'        => fake()->randomElement(self::$units),
            'barcode'     => fake()->optional(0.7)->ean13(),
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
