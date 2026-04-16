<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 3), true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => fake()->sentence(),
            'icon'        => fake()->randomElement(['🍿', '🥤', '🍜', '🌾', '🧂', '🥛', '🍞', '🧊', '🧴', '🧹']),
            'sort_order'  => fake()->numberBetween(1, 100),
            'is_active'   => true,
        ];
    }

    /**
     * State: kategori non-aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
