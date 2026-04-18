<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryFilter = null;
    public string $sort = 'updated_at';
    public string $direction = 'desc';

    /**
     * Update search reset pagination.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update category filter reset pagination.
     */
    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Delete product.
     */
    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->delete();

        session()->flash('message', 'Product deleted successfully.');
    }

    /**
     * Get categories for filter.
     */
    public function with(): array
    {
        return [
            'products' => Product::query()
                ->with('category')
                ->when($this->search, function (Builder $query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->categoryFilter, function (Builder $query) {
                    $query->where('category_id', $this->categoryFilter);
                })
                ->orderBy($this->sort, $this->direction)
                ->paginate(12),
            'categories' => Category::active()->ordered()->get(),
        ];
    }
}; ?>

<div class="py-12 bg-stone-50 min-h-screen font-sans selection:bg-stone-900 selection:text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Floating Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-stone-900">Product Management</h2>
                <p class="mt-2 text-sm text-stone-500">Manage your inventory products, categories, and stock levels with minimalist precision.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('products.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-stone-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-stone-800 focus:bg-stone-800 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-stone-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Product
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-white border border-stone-200 shadow-sm rounded-2xl flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-semibold text-stone-900">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-white/70 backdrop-blur-md border border-stone-200 rounded-3xl p-4 mb-8 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or SKU..." 
                           class="block w-full pl-11 pr-3 py-3 border-none bg-stone-100 rounded-2xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 placeholder-stone-400 text-sm">
                </div>
                
                <div class="relative">
                    <select wire:model.live="categoryFilter" 
                            class="block w-full py-3 border-none bg-stone-100 rounded-2xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 appearance-none text-sm cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end px-2">
                    <div class="flex items-center gap-2 bg-stone-100 px-4 py-2 rounded-xl">
                        <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Total</span>
                        <span class="text-sm font-bold text-stone-900">{{ $products->total() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Card Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div wire:key="product-{{ $product->id }}" class="bg-white border border-stone-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 group flex flex-col relative">
                    <!-- Image Wrapper -->
                    <div class="aspect-square bg-stone-100 overflow-hidden relative">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-stone-200 group-hover:scale-110 transition-transform duration-500">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif

                        <!-- Status Badge -->
                        <div class="absolute top-4 left-4">
                            @if($product->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 text-stone-900 backdrop-blur-md border border-stone-100 shadow-sm uppercase tracking-widest">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-900/10 text-stone-500 backdrop-blur-md border border-white/20 shadow-sm uppercase tracking-widest leading-none">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <!-- Stock Badge -->
                        <div class="absolute top-4 right-4">
                            @php
                                $stockColor = $product->stock > 50 ? 'bg-white/90 text-stone-600' : ($product->stock > 10 ? 'bg-yellow-100/90 text-yellow-700' : 'bg-red-500 text-white');
                            @endphp
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg backdrop-blur-md border border-stone-100 shadow-sm {{ $stockColor }}">
                                {{ $product->stock }} <span class="opacity-60">{{ $product->unit }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs opacity-70">{{ $product->category->icon }}</span>
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">{{ $product->category->name }}</span>
                        </div>

                        <h3 class="text-base font-bold text-stone-900 mb-1 leading-tight group-hover:text-stone-700 transition-colors">{{ $product->name }}</h3>
                        <p class="text-[11px] font-mono text-stone-400 mb-4">{{ $product->sku }}</p>

                        <div class="mt-auto pt-4 border-t border-stone-50 flex items-center justify-between">
                            <div class="text-lg font-black text-stone-900 tracking-tight">
                                {{ $product->formatted_price }}
                            </div>

                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                <a href="{{ route('products.edit', $product->id) }}" class="p-2 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition-all" title="Edit Product">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.138 2.362a2.25 2.25 0 013.182 3.182L9 16.5l-4 1 1-4 10.138-10.138z"/></svg>
                                </a>
                                <button type="button"
                                        wire:click="deleteProduct({{ $product->id }})" 
                                        wire:confirm="Are you sure you want to delete this product?"
                                        class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Delete Product">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="flex flex-col items-center">
                        <div class="h-20 w-20 bg-stone-100 rounded-full flex items-center justify-center text-stone-300 mb-6">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3.586a1 1 0 00-.707.293l-1.414 1.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 009.586 13H4"/></svg>
                        </div>
                        <p class="text-stone-500 font-bold">No products found.</p>
                        <button wire:click="$set('search', ''); $set('categoryFilter', null);" class="mt-2 text-xs font-bold text-stone-900 border-b-2 border-stone-900 cursor-pointer uppercase tracking-widest pb-0.5">Clear filters</button>
                    </div>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="mt-12 px-6 py-8 bg-white border border-stone-200 rounded-3xl shadow-sm">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
