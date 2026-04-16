<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('products', 'products.index')->name('products.index');
    Route::view('products/create', 'products.create')->name('products.create');
    Route::get('products/{product}/edit', function (\App\Models\Product $product) {
        return view('products.edit', ['product' => $product]);
    })->name('products.edit');
});

require __DIR__.'/auth.php';
