<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public ?Product $product = null;

    public int $category_id;
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public ?float $price = null;
    public ?float $cost_price = null;
    public ?int $stock = null;
    public string $unit = 'pcs';
    public ?string $barcode = null;
    public bool $is_active = true;

    /**
     * Mount state.
     */
    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->category_id = $product->category_id;
            $this->name = $product->name;
            $this->sku = $product->sku;
            $this->description = $product->description ?? '';
            $this->price = (float) $product->price;
            $this->cost_price = $product->cost_price ? (float) $product->cost_price : null;
            $this->stock = $product->stock;
            $this->unit = $product->unit;
            $this->barcode = $product->barcode;
            $this->is_active = $product->is_active;
        } else {
            // Defaults for new product
            $this->category_id = Category::ordered()->first()?->id ?? 0;
        }
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($this->product?->id)],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->product?->id)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($this->product?->id)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Save/Update product.
     */
    public function save(): void
    {
        $data = $this->validate();

        if ($this->product) {
            $this->product->update($data);
            $message = 'Product updated successfully.';
        } else {
            Product::create($data);
            $message = 'Product created successfully.';
        }

        session()->flash('message', $message);
        $this->redirect(route('products.index'), navigate: true);
    }

    /**
     * Data for the view.
     */
    public function with(): array
    {
        return [
            'categories' => Category::active()->ordered()->get(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-stone-200 rounded-3xl shadow-xl shadow-stone-200/50 overflow-hidden">
        <!-- Header -->
        <div class="px-8 py-6 bg-stone-900 border-b border-stone-800">
            <h3 class="text-xl font-bold text-white tracking-tight">
                {{ $product ? 'Edit Product Details' : 'Create New Product' }}
            </h3>
            <p class="text-stone-400 text-sm mt-1">
                {{ $product ? "Update information for {$product->sku}" : "Fill in the details to add a new item to your inventory." }}
            </p>
        </div>

        <form wire:submit="save" class="p-8 space-y-8">
            <!-- Basic Information Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-input-label for="name" :value="__('Product Name')" class="text-stone-600 font-semibold mb-2" />
                    <x-text-input wire:model="name" id="name" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="sku" :value="__('SKU (Stock Keeping Unit)')" class="text-stone-600 font-semibold mb-2" />
                    <x-text-input wire:model="sku" id="sku" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all uppercase" required />
                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="category_id" :value="__('Category')" class="text-stone-600 font-semibold mb-2" />
                    <select wire:model="category_id" id="category_id" class="block w-full py-2 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all text-stone-900">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>
            </div>

            <div class="border-t border-stone-100 pt-8">
                <x-input-label for="description" :value="__('Description (Optional)')" class="text-stone-600 font-semibold mb-2" />
                <textarea wire:model="description" id="description" rows="3" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all text-stone-900"></textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- Pricing & Inventory Section -->
            <div class="border-t border-stone-100 pt-8">
                <h4 class="text-sm font-bold text-stone-400 uppercase tracking-widest mb-6">Pricing & Inventory</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="price" :value="__('Selling Price (Rp)')" class="text-stone-600 font-semibold mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 font-mono text-sm">Rp</div>
                            <x-text-input wire:model="price" id="price" type="number" step="0.01" class="block w-full pl-10 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        </div>
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="cost_price" :value="__('Cost Price (Rp)')" class="text-stone-600 font-semibold mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 font-mono text-sm">Rp</div>
                            <x-text-input wire:model="cost_price" id="cost_price" type="number" step="0.01" class="block w-full pl-10 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" />
                        </div>
                        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="stock" :value="__('Current Stock')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="stock" id="stock" type="number" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit" :value="__('Unit (pcs, kg, etc)')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="unit" id="unit" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Details Section -->
            <div class="border-t border-stone-100 pt-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="barcode" :value="__('Barcode (Optional)')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="barcode" id="barcode" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" />
                        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                    </div>

                    <div class="flex items-center pt-8">
                        <label for="is_active" class="inline-flex items-center cursor-pointer">
                            <input wire:model="is_active" id="is_active" type="checkbox" class="rounded-lg bg-stone-100 border-stone-200 text-stone-900 shadow-sm focus:ring-stone-900 transition-all h-5 w-5">
                            <span class="ms-3 text-sm font-bold text-stone-700">Set as Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-8 border-t border-stone-100 flex items-center justify-between">
                <a href="{{ route('products.index') }}" class="text-sm font-bold text-stone-400 hover:text-stone-900 transition-colors uppercase tracking-widest">
                    Cancel
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-stone-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-stone-800 focus:bg-stone-800 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-stone-200">
                    <span wire:loading.remove>Save Product</span>
                    <span wire:loading>Processing...</span>
                </button>
            </div>
        </form>
    </div>
</div>
