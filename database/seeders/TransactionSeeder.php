<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create(['email' => 'kasir@nirva.com']);
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('Tidak ada produk untuk seeding transaksi. Jalankan ProductSeeder dahulu.');
            return;
        }

        // 1. Seed Pelanggan
        $customers = Customer::factory()->count(20)->create();

        // 2. Seed Transaksi (50 transaksi)
        for ($i = 0; $i < 50; $i++) {
            DB::transaction(function () use ($user, $products, $customers) {
                $customer = rand(0, 1) ? $customers->random() : null;
                $date = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                
                // Pilih 1-5 produk secara acak
                $selectedProducts = $products->random(rand(1, 5));
                $items = [];
                $subtotal = 0;

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $itemSubtotal = $product->price * $qty;
                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_sku'  => $product->sku,
                        'quantity'     => $qty,
                        'unit_price'   => $product->price,
                        'cost_price'   => $product->cost_price,
                        'subtotal'     => $itemSubtotal,
                    ];
                }

                // Global Discount & Tax
                $taxRate = rand(0, 1) ? 11 : 0;
                $taxAmount = $subtotal * ($taxRate / 100);
                $grandTotal = $subtotal + $taxAmount;
                $paymentMethod = collect(['cash', 'qris', 'debit'])->random();
                $amountPaid = ceil($grandTotal / 1000) * 1000;
                if ($paymentMethod !== 'cash') $amountPaid = $grandTotal;

                $transaction = Transaction::create([
                    'user_id'           => $user->id,
                    'customer_id'       => $customer?->id,
                    'transaction_date'  => $date,
                    'subtotal'          => $subtotal,
                    'tax_rate'          => $taxRate,
                    'tax_amount'        => $taxAmount,
                    'grand_total'       => $grandTotal,
                    'payment_method'    => $paymentMethod,
                    'amount_paid'       => $amountPaid,
                    'change_amount'     => $amountPaid - $grandTotal,
                    'status'            => collect(['completed', 'completed', 'completed', 'voided'])->random(),
                    'created_at'        => $date,
                    'updated_at'        => $date,
                ]);

                foreach ($items as $item) {
                    $item['transaction_id'] = $transaction->id;
                    $item['created_at'] = $date;
                    $item['updated_at'] = $date;
                    TransactionItem::create($item);
                }
            });
        }

        $this->command->info('Berhasil seeding 50 transaksi.');
    }
}
