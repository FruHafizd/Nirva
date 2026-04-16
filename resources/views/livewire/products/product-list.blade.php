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
                ->paginate(10),
            'categories' => Category::active()->ordered()->get(),
        ];
    }
}; ?>

<div class="py-12 bg-stone-50 min-h-screen font-sans selection:bg-yellow-200">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Floating Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-stone-900 font-display">Product Management</h2>
                <p class="mt-2 text-sm text-stone-500">Manage your inventory products, categories, and stock levels with minimalist precision.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('products.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-stone-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-stone-800 focus:bg-stone-800 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm shadow-stone-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Product
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-white border-l-4 border-yellow-600 shadow-sm rounded-r-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium text-stone-800">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-white/70 backdrop-blur-md border border-stone-200 rounded-2xl p-4 mb-6 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or SKU..." 
                           class="block w-full pl-10 pr-3 py-2.5 border-none bg-stone-100 rounded-xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 placeholder-stone-400">
                </div>
                
                <div class="relative">
                    <select wire:model.live="categoryFilter" 
                            class="block w-full py-2.5 border-none bg-stone-100 rounded-xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 appearance-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 px-2">
                    <span class="text-xs font-medium text-stone-400 uppercase tracking-wider">Total:</span>
                    <span class="text-sm font-bold text-stone-900">{{ $products->total() }}</span>
                </div>
            </div>
        </div>

        <!-- Product Table Card -->
        <div class="bg-white border border-stone-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100">
                    <thead>
                        <tr class="bg-stone-50/50">
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-widest">Product</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-widest">SKU</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-widest">Category</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-widest">Price</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-stone-500 uppercase tracking-widest">Stock</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-stone-500 uppercase tracking-widest">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($products as $product)
                            <tr class="group hover:bg-stone-50/60 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 bg-stone-100 rounded-lg flex items-center justify-center text-stone-400 overflow-hidden group-hover:bg-white transition-colors duration-200">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-stone-900 group-hover:text-stone-700 transition-colors duration-150">{{ $product->name }}</div>
                                            <div class="text-xs text-stone-400">{{ $product->unit }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-mono font-medium text-stone-600 bg-stone-100 rounded-md">{{ $product->sku }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-stone-600">
                                        <span class="mr-2 opacity-80">{{ $product->category->icon }}</span>
                                        <span>{{ $product->category->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-bold text-stone-900 tracking-tight">{{ $product->formatted_price }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $stockColor = $product->stock > 50 ? 'bg-stone-100 text-stone-600' : ($product->stock > 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $stockColor }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 mr-2"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-stone-50 text-stone-400 border border-stone-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.edit', $product->id) }}" class="p-2 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-all duration-200">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.138 2.362a2.25 2.25 0 013.182 3.182L9 16.5l-4 1 1-4 10.138-10.138z"/></svg>
                                        </a>
                                        <button wire:click="deleteProduct({{ $product->id }})" 
                                                wire:confirm="Are you sure you want to delete this product? This action cannot be undone."
                                                class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-16 w-16 bg-stone-50 rounded-full flex items-center justify-center text-stone-200 mb-4">
                                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3.586a1 1 0 00-.707.293l-1.414 1.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 009.586 13H4"/></svg>
                                        </div>
                                        <p class="text-stone-500 font-medium">No products found matching your criteria.</p>
                                        <button wire:click="$set('search', ''); $set('categoryFilter', null);" class="mt-2 text-sm text-stone-900 border-b border-stone-900 font-semibold cursor-pointer">Clear filters</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($products->hasPages())
                <div class="px-6 py-4 bg-stone-50/50 border-t border-stone-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
