<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Livewire\Transactions\PointOfSale;
use Livewire\Livewire;
use Livewire\Volt\Volt;

// ==========================================
// POS SEARCH TESTS
// ==========================================

test('POS search finds product by name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $target = Product::factory()->create(['name' => 'Indomie Goreng Spesial Test', 'is_active' => true, 'stock' => 10]);
    Product::factory()->create(['name' => 'Aqua Botol Mineral Test', 'is_active' => true, 'stock' => 10]);

    Livewire::test(PointOfSale::class)
        ->set('search', 'Indomie')
        ->assertSee('Indomie Goreng Spesial Test')
        ->assertDontSee('Aqua Botol Mineral Test');
});

test('POS search finds product by SKU', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $target = Product::factory()->create(['sku' => 'SKU-SEARCH-001', 'is_active' => true, 'stock' => 10]);
    Product::factory()->create(['sku' => 'SKU-OTHER-999', 'is_active' => true, 'stock' => 10]);

    Livewire::test(PointOfSale::class)
        ->set('search', 'SEARCH-001')
        ->assertSee('SKU-SEARCH-001');
});

test('POS search finds product by barcode', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $target = Product::factory()->create(['barcode' => '8886008999111', 'is_active' => true, 'stock' => 10]);
    Product::factory()->create(['barcode' => '1234567890123', 'is_active' => true, 'stock' => 10]);

    Livewire::test(PointOfSale::class)
        ->set('search', '8886008999111')
        ->assertSee($target->name);
});

test('POS search returns empty state when no products match', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create(['name' => 'Produk Ada', 'is_active' => true, 'stock' => 10]);

    Livewire::test(PointOfSale::class)
        ->set('search', 'xyznonexistent999')
        ->assertSee('Tidak ada produk untuk');
});

test('POS search only shows active products', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create(['name' => 'Produk Aktif Cari', 'is_active' => true, 'stock' => 10]);
    Product::factory()->create(['name' => 'Produk Nonaktif Cari', 'is_active' => false, 'stock' => 10]);

    Livewire::test(PointOfSale::class)
        ->set('search', 'Cari')
        ->assertSee('Produk Aktif Cari')
        ->assertDontSee('Produk Nonaktif Cari');
});

test('POS search limits results to 20', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    Product::factory()->count(30)->forCategory($category)->create([
        'is_active' => true,
        'stock' => 10,
    ]);

    $component = Livewire::test(PointOfSale::class)
        ->set('search', '');

    // The render method limits to 20, so viewData should have max 20
    expect($component->viewData('products')->count())->toBeLessThanOrEqual(20);
});

// ==========================================
// PRODUCT LIST SEARCH TESTS
// ==========================================

test('Product list search finds product by name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create(['name' => 'Chitato Balado Search']);
    Product::factory()->create(['name' => 'Teh Pucuk Search']);

    Volt::test('products.product-list')
        ->set('search', 'Chitato')
        ->assertSee('Chitato Balado Search')
        ->assertDontSee('Teh Pucuk Search');
});

test('Product list search finds product by barcode', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create(['name' => 'Produk Barcode Test', 'barcode' => '9999888877776']);
    Product::factory()->create(['name' => 'Produk Lain Test', 'barcode' => '1111222233334']);

    Volt::test('products.product-list')
        ->set('search', '9999888877776')
        ->assertSee('Produk Barcode Test')
        ->assertDontSee('Produk Lain Test');
});

test('Product list search resets pagination', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    Product::factory()->count(20)->forCategory($category)->create();
    Product::factory()->create(['name' => 'Pencarian Khusus Unik']);

    Volt::test('products.product-list')
        ->set('search', 'Pencarian Khusus Unik')
        ->assertSee('Pencarian Khusus Unik');
});

test('Product list search shows clear filter button on empty result', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create(['name' => 'Produk Normal']);

    Volt::test('products.product-list')
        ->set('search', 'xyznonexistent')
        ->assertSee('Tidak ada produk ditemukan')
        ->assertSee('Hapus filter');
});
