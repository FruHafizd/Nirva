<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number', 'user_id', 'customer_id', 'transaction_date',
    'subtotal', 'discount_type', 'discount_value', 'discount_amount',
    'tax_rate', 'tax_amount', 'grand_total', 'payment_method',
    'payment_reference', 'amount_paid', 'change_amount', 'status', 'notes',
])]
class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'subtotal'         => 'decimal:2',
            'discount_value'   => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'tax_rate'         => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'amount_paid'      => 'decimal:2',
            'change_amount'    => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (!$transaction->invoice_number) {
                $transaction->invoice_number = static::generateInvoiceNumber();
            }
            if (!$transaction->transaction_date) {
                $transaction->transaction_date = now();
            }
        });
    }

    /**
     * Generate unique invoice number: INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";
        
        $lastTransaction = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastSequence = (int) substr($lastTransaction->invoice_number, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return $prefix . $newSequence;
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Transaksi diproses oleh satu user (kasir).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaksi mungkin milik satu pelanggan.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Satu transaksi memiliki banyak item.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', Carbon::today());
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Format Grand Total ke Rupiah.
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    /**
     * Hitung total profit dari transaksi ini.
     */
    public function getProfitAttribute(): float
    {
        return (float) $this->items->sum(fn($item) => ($item->unit_price - $item->cost_price) * $item->quantity - $item->discount_amount);
    }
}
