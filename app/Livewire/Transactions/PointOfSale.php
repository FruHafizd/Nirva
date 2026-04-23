<?php

namespace App\Livewire\Transactions;

use App\Models\Customer;
use App\Models\Product;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Title('Kasir (POS)')]
class PointOfSale extends Component
{
    /**
     * Handle barcode scan result.
     */
    #[\Livewire\Attributes\On('barcode-result')]
    public function handleBarcodeResult(array $data): void
    {
        if ($data['found'] && $data['product_id']) {
            $this->addToCart($data['product_id']);
            
            // Flash notification
            $this->dispatch('notify', [
                'message' => "✓ {$data['product_name']} ditambahkan",
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notify', [
                'message' => "Produk dengan barcode {$data['barcode']} tidak ditemukan",
                'type' => 'error'
            ]);
        }
    }

    // Search & Data
    public $search = '';
    public $cart = [];
    public $customers = [];
    
    // Transaction Props
    public $selectedCustomerId = null;
    public $discountType = 'fixed';
    public $discountValue = 0;
    public $taxRate = 0; // Default 0 as per feedback
    public $paymentMethod = 'cash';
    public $amountPaid = 0;
    public $notes = '';

    // Totals
    public $subtotal = 0;
    public $discountAmount = 0;
    public $taxAmount = 0;
    public $grandTotal = 0;
    public $changeAmount = 0;

    public function mount()
    {
        $this->customers = Customer::active()->get();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['cart', 'discountType', 'discountValue', 'taxRate', 'amountPaid'])) {
            $this->recalculate();
        }
    }

    public function addToCart(int $productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            session()->flash('error', "Stok {$product->name} habis!");
            return;
        }

        if (collect($this->cart)->contains('product_id', $productId)) {
            $index = collect($this->cart)->search(fn($item) => $item['product_id'] === $productId);
            if ($this->cart[$index]['quantity'] < $product->stock) {
                $this->cart[$index]['quantity']++;
            } else {
                session()->flash('error', "Stok {$product->name} tidak cukup untuk ditambah lagi!");
            }
        } else {
            $this->cart[] = [
                'product_id'     => $product->id,
                'name'           => $product->name,
                'price'          => (float) $product->price,
                'quantity'       => 1,
                'discount_type'  => 'fixed',
                'discount_value' => 0,
            ];
        }

        $this->recalculate();
    }

    public function removeFromCart(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->recalculate();
    }

    public function updateQuantity(int $index, int $qty)
    {
        if ($qty <= 0) {
            $this->removeFromCart($index);
            return;
        }

        $product = Product::find($this->cart[$index]['product_id']);
        if ($qty > $product->stock) {
            $this->cart[$index]['quantity'] = $product->stock;
            session()->flash('error', "Maksimal stok {$product->name} adalah {$product->stock}.");
        } else {
            $this->cart[$index]['quantity'] = $qty;
        }

        $this->recalculate();
    }

    public function recalculate()
    {
        $service = app(TransactionService::class);
        $calc = $service->calculateTotals(
            $this->cart,
            $this->discountType,
            (float) $this->discountValue,
            (float) $this->taxRate
        );

        $this->subtotal       = $calc['subtotal'];
        $this->discountAmount = $calc['discount_amount'];
        $this->taxAmount      = $calc['tax_amount'];
        $this->grandTotal     = $calc['grand_total'];
        
        $this->changeAmount = (float) $this->amountPaid - $this->grandTotal;
    }

    /**
     * Set the amount paid from quick-pay buttons.
     */
    public function setAmountPaid(float $amount)
    {
        $this->amountPaid = $amount;
        $this->recalculate();
    }

    /**
     * Set the amount paid to the exact grand total.
     */
    public function setExactAmount()
    {
        $this->amountPaid = (float) $this->grandTotal;
        $this->recalculate();
    }

    public function submit(TransactionService $service)
    {
        $this->validate([
            'cart'           => 'required|array|min:1',
            'paymentMethod'  => 'required',
            'amountPaid'     => 'required|numeric|min:' . $this->grandTotal,
        ], [
            'amountPaid.min' => 'Jumlah bayar kurang dari total belanja.',
            'cart.required'  => 'Keranjang masih kosong.',
        ]);

        try {
            $transaction = $service->createTransaction([
                'user_id'         => Auth::id(),
                'customer_id'     => $this->selectedCustomerId,
                'items'           => $this->cart,
                'discount_type'   => $this->discountType,
                'discount_value'  => (float) $this->discountValue,
                'tax_rate'        => (float) $this->taxRate,
                'payment_method'  => $this->paymentMethod,
                'amount_paid'     => (float) $this->amountPaid,
                'notes'           => $this->notes,
            ]);

            session()->flash('success', "Transaksi {$transaction->invoice_number} berhasil!");
            $this->reset('cart', 'selectedCustomerId', 'discountValue', 'taxRate', 'amountPaid', 'notes');
            $this->recalculate();
        } catch (\Exception $e) {
            session()->flash('error', "Gagal memproses transaksi: " . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::active()
            ->select('id', 'name', 'sku', 'barcode', 'price', 'stock', 'unit', 'image_url')
            ->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%");
            })
            ->limit(20)
            ->get();

        return view('livewire.transactions.point-of-sale', [
            'products' => $products
        ]);
    }
}
