<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50000, 1000000);
        $taxRate = $this->faker->randomElement([0, 11]);
        $taxAmount = $subtotal * ($taxRate / 100);
        $grandTotal = $subtotal + $taxAmount;
        $amountPaid = ceil($grandTotal / 50000) * 50000;

        return [
            'user_id'           => User::first()?->id ?? User::factory(),
            'customer_id'       => Customer::factory(),
            'transaction_date'  => $this->faker->dateTimeBetween('-1 month', 'now'),
            'subtotal'          => $subtotal,
            'tax_rate'          => $taxRate,
            'tax_amount'        => $taxAmount,
            'grand_total'       => $grandTotal,
            'payment_method'    => $this->faker->randomElement(['cash', 'qris', 'debit']),
            'amount_paid'       => $amountPaid,
            'change_amount'     => $amountPaid - $grandTotal,
            'status'            => 'completed',
        ];
    }
}
