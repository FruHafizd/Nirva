<?php

use App\Models\Category;
use App\Models\Product;

// ==========================================
// DATABASE & MODEL TESTS
// ==========================================

test('products table exists and has correct columns', function () {
    $product = Product::factory()->create();
    
    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->id)->toBeInt()
        ->and($product->category_id)->toBeInt()
        ->and($product->name)->toBeString()
        ->and($product->sku)->toBeString()
        ->and($product->price)->not->toBeNull()
        ->and($product->stock)->toBeInt()
        ->and($product->unit)->toBeString()
        ->and($product->is_active)->toBeBool();
});

test('product name must be unique', function () {
    Product::factory()->create(['name' => 'Indomie Goreng']);
    
    expect(fn () => Product::factory()->create(['name' => 'Indomie Goreng']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('product SKU must be unique', function () {
    Product::factory()->create(['sku' => 'SKU-001']);
    
    expect(fn () => Product::factory()->create(['sku' => 'SKU-001']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('product barcode must be unique when not null', function () {
    Product::factory()->create(['barcode' => '1234567890123']);
    
    expect(fn () => Product::factory()->create(['barcode' => '1234567890123']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

// ==========================================
// RELATIONSHIP TESTS
// ==========================================

test('product belongs to a category', function () {
    $category = Category::factory()->create(['name' => 'Minuman Test']);
    $product = Product::factory()->forCategory($category)->create();
    
    expect($product->category)->toBeInstanceOf(Category::class)
        ->and($product->category->name)->toBe('Minuman Test');
});

test('product requires a valid category_id', function () {
    expect(fn () => Product::factory()->create(['category_id' => 99999]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

// ==========================================
// CAST TESTS
// ==========================================

test('product price is cast to decimal', function () {
    $product = Product::factory()->create(['price' => 15500.50]);
    
    expect((float) $product->fresh()->price)->toBe(15500.50);
});

test('product is_active is cast to boolean', function () {
    $product = Product::factory()->create(['is_active' => 1]);
    
    expect($product->fresh()->is_active)->toBeBool()->toBeTrue();
});

// ==========================================
// SCOPE TESTS
// ==========================================

test('scope active returns only active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);
    
    expect(Product::active()->count())->toBe(3);
});

test('scope byCategory filters correctly', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();
    Product::factory()->count(3)->forCategory($cat1)->create();
    Product::factory()->count(2)->forCategory($cat2)->create();
    
    expect(Product::byCategory($cat1->id)->count())->toBe(3);
});

test('scope byCategorySlug filters correctly', function () {
    $category = Category::factory()->create(['slug' => 'minuman-test']);
    Product::factory()->count(2)->forCategory($category)->create();
    Product::factory()->count(3)->create(); // random categories
    
    expect(Product::byCategorySlug('minuman-test')->count())->toBe(2);
});

// ==========================================
// ACCESSOR TESTS
// ==========================================

test('formatted price returns Rupiah format', function () {
    $product = Product::factory()->create(['price' => 15500]);
    
    expect($product->formatted_price)->toBe('Rp 15.500');
});

test('profit margin is calculated correctly', function () {
    $product = Product::factory()->create(['price' => 10000, 'cost_price' => 8000]);
    
    expect($product->profit_margin)->toBe(25.0);
});

test('profit margin returns null when no cost price', function () {
    $product = Product::factory()->create(['cost_price' => null]);
    
    expect($product->profit_margin)->toBeNull();
});

// ==========================================
// FACTORY STATE TESTS
// ==========================================

test('factory out of stock state works', function () {
    $product = Product::factory()->outOfStock()->create();
    
    expect($product->stock)->toBe(0);
});

test('factory inactive state works', function () {
    $product = Product::factory()->inactive()->create();
    
    expect($product->is_active)->toBeFalse();
});

test('factory forCategory state works', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->forCategory($category)->create();
    
    expect($product->category_id)->toBe($category->id);
});
