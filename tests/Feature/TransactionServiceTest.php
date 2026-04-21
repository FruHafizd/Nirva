<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create([
        'price' => 10000,
        'cost_price' => 8000,
        'stock' => 10,
    ]);
    $this->service = new TransactionService();
});

test('it can create a transaction and deduct stock', function () {
    $data = [
        'user_id' => $this->user->id,
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 2]
        ],
        'tax_rate' => 10,
        'payment_method' => 'cash',
        'amount_paid' => 30000,
    ];

    $transaction = $this->service->createTransaction($data);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect((float)$transaction->subtotal)->toEqual(20000.0);
    expect((float)$transaction->tax_amount)->toEqual(2000.0);
    expect((float)$transaction->grand_total)->toEqual(22000.0);
    expect($transaction->items)->toHaveCount(1);
    
    // Check stock
    $this->product->refresh();
    expect($this->product->stock)->toBe(8);
});

test('it throws an exception if stock is not enough', function () {
    $data = [
        'user_id' => $this->user->id,
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 11]
        ],
        'payment_method' => 'cash',
        'amount_paid' => 150000,
    ];

    expect(fn() => $this->service->createTransaction($data))
        ->toThrow(Exception::class, "Stok untuk produk {$this->product->name} tidak mencukupi.");
});

test('it can void a transaction and restore stock', function () {
    $data = [
        'user_id' => $this->user->id,
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 5]
        ],
        'payment_method' => 'cash',
        'amount_paid' => 100000,
    ];

    $transaction = $this->service->createTransaction($data);
    
    $this->product->refresh();
    expect($this->product->stock)->toBe(5);

    $this->service->voidTransaction($transaction);

    expect($transaction->refresh()->status)->toBe('voided');
    $this->product->refresh();
    expect($this->product->stock)->toBe(10);
});
