<?php

use App\Models\Category;
use App\Models\Product;

// ==========================================
// DATABASE & MODEL TESTS
// ==========================================

test('categories table exists and can store data', function () {
    $category = Category::factory()->create();
    
    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->id)->toBeInt()
        ->and($category->name)->toBeString()
        ->and($category->slug)->toBeString()
        ->and($category->is_active)->toBeBool();
});

test('category name must be unique', function () {
    Category::factory()->create(['name' => 'Minuman']);
    
    expect(fn () => Category::factory()->create(['name' => 'Minuman']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('category slug must be unique', function () {
    Category::factory()->create(['slug' => 'minuman']);
    
    expect(fn () => Category::factory()->create(['slug' => 'minuman']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

// ==========================================
// RELATIONSHIP TESTS
// ==========================================

test('category has many products', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->forCategory($category)->create();
    
    expect($category->products)->toHaveCount(3);
});

test('deleting category cascades to products', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->forCategory($category)->create();
    
    $category->delete();
    
    expect(Product::where('category_id', $category->id)->count())->toBe(0);
});

// ==========================================
// SCOPE TESTS
// ==========================================

test('scope active returns only active categories', function () {
    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);
    
    expect(Category::active()->count())->toBe(3);
});

test('scope ordered sorts by sort_order', function () {
    Category::factory()->create(['sort_order' => 3, 'name' => 'Third']);
    Category::factory()->create(['sort_order' => 1, 'name' => 'First']);
    Category::factory()->create(['sort_order' => 2, 'name' => 'Second']);
    
    $ordered = Category::ordered()->pluck('name')->toArray();
    
    expect($ordered)->toBe(['First', 'Second', 'Third']);
});
