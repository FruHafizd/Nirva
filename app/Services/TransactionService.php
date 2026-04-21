<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction with its items.
     * 
     * @param array $data {
     *     user_id: int,
     *     customer_id: int|null,
     *     items: array [
     *         ['product_id' => int, 'quantity' => int, 'discount_type' => string, 'discount_value' => float]
     *     ],
     *     discount_type: string|null,
     *     discount_value: float,
     *     tax_rate: float,
     *     payment_method: string,
     *     amount_paid: float,
     *     notes: string|null
     * }
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            // 1. Calculate totals temporarily to validate
            $calc = $this->calculateTotals(
                $data['items'],
                $data['discount_type'] ?? null,
                $data['discount_value'] ?? 0,
                $data['tax_rate'] ?? 0
            );

            // 2. Create Transaction Header
            $transaction = Transaction::create([
                'user_id'           => $data['user_id'],
                'customer_id'       => $data['customer_id'] ?? null,
                'transaction_date'  => now(),
                'subtotal'          => $calc['subtotal'],
                'discount_type'     => $data['discount_type'] ?? null,
                'discount_value'    => $data['discount_value'] ?? 0,
                'discount_amount'   => $calc['discount_amount'],
                'tax_rate'          => $data['tax_rate'] ?? 0,
                'tax_amount'        => $calc['tax_amount'],
                'grand_total'       => $calc['grand_total'],
                'payment_method'    => $data['payment_method'],
                'amount_paid'       => $data['amount_paid'],
                'change_amount'     => $data['amount_paid'] - $calc['grand_total'],
                'status'            => 'completed',
                'notes'             => $data['notes'] ?? null,
            ]);

            // 3. Process Items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Validate Stock
                if ($product->stock < $itemData['quantity']) {
                    throw new Exception("Stok untuk produk {$product->name} tidak mencukupi.");
                }

                // Calculate item discount
                $itemDiscount = $this->calculateItemDiscount(
                    $product->price,
                    $itemData['quantity'],
                    $itemData['discount_type'] ?? null,
                    $itemData['discount_value'] ?? 0
                );

                $itemSubtotal = ($product->price * $itemData['quantity']) - $itemDiscount;

                // Create Transaction Item (Snapshot)
                TransactionItem::create([
                    'transaction_id'  => $transaction->id,
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'product_sku'     => $product->sku,
                    'quantity'        => $itemData['quantity'],
                    'unit_price'      => $product->price,
                    'cost_price'      => $product->cost_price,
                    'discount_type'   => $itemData['discount_type'] ?? null,
                    'discount_value'  => $itemData['discount_value'] ?? 0,
                    'discount_amount' => $itemDiscount,
                    'subtotal'        => $itemSubtotal,
                ]);

                // 4. Update Stock
                $product->decrement('stock', $itemData['quantity']);
            }

            return $transaction;
        });
    }

    /**
     * Calculate all totals for a transaction.
     */
    public function calculateTotals(array $items, ?string $discountType, float $discountValue, float $taxRate): array
    {
        $subtotal = 0;

        foreach ($items as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $itemDiscount = $this->calculateItemDiscount(
                $product->price,
                $itemData['quantity'],
                $itemData['discount_type'] ?? null,
                $itemData['discount_value'] ?? 0
            );
            $subtotal += ($product->price * $itemData['quantity']) - $itemDiscount;
        }

        // Global Discount
        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } elseif ($discountType === 'fixed') {
            $discountAmount = $discountValue;
        }

        $afterDiscount = $subtotal - $discountAmount;

        // Global Tax
        $taxAmount = $afterDiscount * ($taxRate / 100);

        $grandTotal = $afterDiscount + $taxAmount;

        return [
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount'      => $taxAmount,
            'grand_total'     => $grandTotal,
        ];
    }

    /**
     * Calculate discount for a single item.
     */
    private function calculateItemDiscount(float $unitPrice, int $quantity, ?string $type, float $value): float
    {
        if (!$type || $value <= 0) return 0;

        if ($type === 'percentage') {
            return ($unitPrice * $quantity) * ($value / 100);
        } elseif ($type === 'fixed') {
            return $value; // usually per item total discount
        }

        return 0;
    }

    /**
     * Void a transaction and restore stock.
     */
    public function voidTransaction(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'voided') {
                throw new Exception("Transaksi sudah dibatalkan sebelumnya.");
            }

            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $transaction->update(['status' => 'voided']);

            return $transaction;
        });
    }
}
