<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Livewire\Volt\Volt;

// ============================================
// Barcode Scanner Feature Tests
// ============================================

test('barcode scanner component is accessible', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertStatus(200)
        ->assertSee('Scan Barcode');
});

test('processBarcode identified existing product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'barcode' => '8991234567890',
    ]);

    Volt::test('products.barcode-scanner')
        ->call('processBarcode', '8991234567890')
        ->assertSet('scannedBarcode', '8991234567890')
        ->assertSet('foundProduct.id', $product->id)
        ->assertSet('showResult', true);
});

test('processBarcode handles new barcode by offering registration', function () {
    Volt::test('products.barcode-scanner')
        ->call('processBarcode', '999888777666')
        ->assertSet('foundProduct', null)
        ->assertSet('showResult', true);
});

test('product create form consumes barcode query parameter', function () {
    $user = User::factory()->create();
    Category::factory()->create();

    $this->actingAs($user)
        ->get(route('products.create', ['barcode' => '777666555']))
        ->assertStatus(200)
        ->assertSee('777666555');
});

test('resetScanner clears internal state', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create(['barcode' => '123']);

    Volt::test('products.barcode-scanner')
        ->call('processBarcode', '123')
        ->call('resetScanner')
        ->assertSet('scannedBarcode', '')
        ->assertSet('foundProduct', null)
        ->assertSet('showResult', false);
});
