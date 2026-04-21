<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory();
        $quantity = $this->faker->numberBetween(1, 5);
        $subtotal = $product->price * $quantity;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'product_sku'    => $product->sku,
            'quantity'       => $quantity,
            'unit_price'     => $product->price,
            'cost_price'     => $product->cost_price,
            'subtotal'       => $subtotal,
        ];
    }
}
