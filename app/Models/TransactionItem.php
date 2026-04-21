<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id', 'product_id', 'product_name', 'product_sku',
    'quantity', 'unit_price', 'cost_price', 'discount_type',
    'discount_value', 'discount_amount', 'subtotal',
])]
class TransactionItem extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'unit_price'      => 'decimal:2',
            'cost_price'      => 'decimal:2',
            'discount_value'  => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Detail item milik satu transaksi.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Detail item merujuk ke satu produk.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Format Subtotal ke Rupiah.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Hitung profit dari item ini.
     */
    public function getProfitAttribute(): float
    {
        return (float) (($this->unit_price - $this->cost_price) * $this->quantity - $this->discount_amount);
    }
}
