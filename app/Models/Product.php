<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id', 'name', 'sku', 'description',
    'price', 'cost_price', 'stock', 'unit',
    'barcode', 'is_active', 'image_url',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            if ($product->image_url && str_contains($product->image_url, '/storage/')) {
                $path = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'price'       => 'decimal:2',
            'cost_price'  => 'decimal:2',
            'stock'       => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Produk milik satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope: hanya produk aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter berdasarkan category_id.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: filter berdasarkan category slug.
     */
    public function scopeByCategorySlug($query, string $slug)
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Format harga ke Rupiah.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Format harga modal ke Rupiah.
     */
    public function getFormattedCostPriceAttribute(): ?string
    {
        if ($this->cost_price === null) return null;
        return 'Rp ' . number_format($this->cost_price, 0, ',', '.');
    }

    /**
     * Hitung margin profit.
     */
    public function getProfitMarginAttribute(): ?float
    {
        if ($this->cost_price === null || (float) $this->cost_price === 0.0) return null;
        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }
}
