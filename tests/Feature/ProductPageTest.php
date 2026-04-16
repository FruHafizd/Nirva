<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Volt\Volt;

// ==========================================
// AUTH & ACCESS TESTS
// ==========================================

test('product list page requires authentication', function () {
    $this->get('/products')
        ->assertRedirect('/login');
});

test('authenticated user can access product list page', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get('/products')
        ->assertOk();
});

test('product create page requires authentication', function () {
    $this->get('/products/create')
        ->assertRedirect('/login');
});

test('authenticated user can access product create page', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get('/products/create')
        ->assertOk();
});

// ==========================================
// LIVEWIRE COMPONENT TESTS
// ==========================================

test('product list page shows products with category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Minuman']);
    $product = Product::factory()->forCategory($category)->create(['name' => 'Aqua Test']);
    
    $this->actingAs($user)
        ->get('/products')
        ->assertSee('Aqua Test')
        ->assertSee('Minuman');
});

test('product list page can search products', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Aqua Botol']);
    Product::factory()->create(['name' => 'Indomie Goreng']);
    
    $this->actingAs($user);

    Volt::test('products.product-list')
        ->set('search', 'Aqua')
        ->assertSee('Aqua Botol')
        ->assertDontSee('Indomie Goreng');
});

test('product list page can filter by category', function () {
    $user = User::factory()->create();
    $catMinuman = Category::factory()->create(['name' => 'Minuman']);
    $catSnack = Category::factory()->create(['name' => 'Snack']);
    Product::factory()->forCategory($catMinuman)->create(['name' => 'Aqua']);
    Product::factory()->forCategory($catSnack)->create(['name' => 'Chitato']);
    
    $this->actingAs($user);

    Volt::test('products.product-list')
        ->set('categoryFilter', $catMinuman->id)
        ->assertSee('Aqua')
        ->assertDontSee('Chitato');
});

test('product can be deleted from list', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    $this->actingAs($user);

    Volt::test('products.product-list')
        ->call('deleteProduct', $product->id);
    
    expect(Product::find($product->id))->toBeNull();
});

// ==========================================
// CRUD OPERATION TESTS
// ==========================================

test('product can be created via form', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $this->actingAs($user);

    Volt::test('products.product-form')
        ->set('category_id', $category->id)
        ->set('name', 'Test Product Baru')
        ->set('sku', 'TST-999')
        ->set('price', 10000)
        ->set('stock', 50)
        ->set('unit', 'pcs')
        ->call('save')
        ->assertHasNoErrors();
    
    expect(Product::where('sku', 'TST-999')->exists())->toBeTrue();
});

test('product creation validates required fields', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);

    Volt::test('products.product-form')
        ->set('name', '')
        ->set('sku', '')
        ->set('category_id', '')
        ->set('price', '')
        ->call('save')
        ->assertHasErrors(['name', 'sku', 'category_id', 'price']);
});

test('product creation validates category exists', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);

    Volt::test('products.product-form')
        ->set('category_id', 99999)  // non-existent category
        ->set('name', 'Test')
        ->set('sku', 'TST-000')
        ->set('price', 5000)
        ->set('stock', 10)
        ->set('unit', 'pcs')
        ->call('save')
        ->assertHasErrors(['category_id']);
});
