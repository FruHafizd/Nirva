<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'phone', 'email', 'address', 'membership_type', 'notes', 'is_active',
])]
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Pelanggan memiliki banyak transaksi.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope: hanya pelanggan aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter berdasarkan tipe member.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('membership_type', $type);
    }
}
