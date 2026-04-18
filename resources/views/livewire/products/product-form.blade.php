<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

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
    public $photo;
    public ?string $image_url = null;

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
            $this->image_url = $product->image_url;
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
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Save/Update product.
     */
    public function save(): void
    {
        $data = $this->validate();

        if ($this->photo) {
            $path = $this->photo->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        // Remove photo from data before saving to DB
        unset($data['photo']);

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
            <!-- Product Photo Section -->
            <div>
                <x-input-label for="photo" :value="__('Product Photo')" class="text-stone-600 font-semibold mb-4" />
                <div class="flex items-start gap-6">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-2xl bg-stone-100 border-2 border-dashed border-stone-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-stone-900">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($image_url)
                                <img src="{{ $image_url }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-stone-900/10 backdrop-blur-[2px] rounded-2xl flex items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-stone-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center">
                            <label for="photo" class="px-4 py-2 bg-stone-100 text-stone-900 text-sm font-bold rounded-xl cursor-pointer hover:bg-stone-200 transition-colors">
                                Choose Image
                                <input wire:model="photo" id="photo" type="file" class="hidden" accept="image/*">
                            </label>
                            @if ($photo || $image_url)
                                <button type="button" wire:click="$set('photo', null); $set('image_url', null)" class="ml-4 text-xs font-bold text-red-500 hover:text-red-600 transition-colors">
                                    Remove
                                </button>
                            @endif
                        </div>
                        <p class="text-xs text-stone-400">JPG, PNG, WebP. Maximum 2MB.</p>
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                    </div>
                </div>
            </div>

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
