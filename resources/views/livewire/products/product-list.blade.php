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
     * Handle barcode scan result.
     */
    #[Livewire\Attributes\On('barcode-result')]
    public function handleBarcodeResult(array $data): void
    {
        $this->scanResult = $data;
    }

    public ?array $scanResult = null;

    public function clearScanResult(): void
    {
        $this->scanResult = null;
    }

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

        session()->flash('message', 'Produk berhasil dihapus.');
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
                <h2 class="text-3xl font-bold tracking-tight text-stone-900">Manajemen Produk</h2>
                <p class="mt-2 text-sm text-stone-500">Kelola produk inventaris, kategori, dan tingkat stok Anda dengan presisi minimalis.</p>
            </div>
            
            <div class="flex items-center gap-3" x-data="{ showScanner: false }" @close-modal.window="showScanner = false">
                <button @click="showScanner = true"
                        class="inline-flex items-center px-4 py-2.5 bg-white border-2 border-stone-900 rounded-xl font-bold text-xs text-stone-900 uppercase tracking-widest hover:bg-stone-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition-all duration-200 shadow-lg shadow-stone-100 cursor-pointer group">
                    <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    Scan Barcode
                </button>

                <a href="{{ route('products.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-stone-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-stone-800 focus:bg-stone-800 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition-all duration-200 shadow-lg shadow-stone-200 cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Produk Baru
                </a>

                <!-- Scanner Modal Overlay -->
                <div x-show="showScanner" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm"
                     @keydown.escape.window="showScanner = false">
                    
                    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-stone-200"
                         @click.away="showScanner = false"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                        
                        <div class="px-8 py-5 flex items-center justify-between border-b border-stone-100">
                            <div>
                                <h3 class="text-lg font-black text-stone-900 tracking-tight">Scanner Barcode</h3>
                                <p class="text-[10px] text-stone-400 font-bold uppercase tracking-widest mt-0.5">Nirva Digital Artisan</p>
                            </div>
                            <button @click="showScanner = false" class="p-2 text-stone-400 hover:text-stone-900 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="p-8">
                            <livewire:products.barcode-scanner />

                            @if($scanResult)
                                <div class="mt-8 pt-8 border-t border-stone-100 space-y-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                                    @if($scanResult['found'])
                                        <div class="p-6 bg-stone-900 rounded-2xl shadow-xl shadow-stone-200 text-white flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[10px] uppercase tracking-widest text-stone-400 font-bold mb-0.5">Produk Ditemukan</p>
                                                <h5 class="font-bold truncate">{{ $scanResult['product_name'] }}</h5>
                                                <p class="text-xs text-stone-300">{{ $scanResult['barcode'] }} • Rp {{ number_format($scanResult['product_price'], 0, ',', '.') }}</p>
                                            </div>
                                            <a href="{{ route('products.edit', $scanResult['product_id']) }}" 
                                               wire:navigate
                                               class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors cursor-pointer">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <div class="p-6 bg-white border border-stone-200 rounded-2xl shadow-sm space-y-4">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-stone-900">Barcode Baru Terdeteksi</p>
                                                    <p class="text-xs text-stone-500 mt-1">Produk dengan barcode <span class="font-mono font-bold text-stone-900 underline">{{ $scanResult['barcode'] }}</span> belum terdaftar di sistem.</p>
                                                </div>
                                            </div>
                                            <a href="{{ route('products.create', ['barcode' => $scanResult['barcode']]) }}" 
                                               wire:navigate
                                               class="flex-1 inline-flex items-center justify-center w-full px-4 py-2.5 bg-stone-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-800 transition-all cursor-pointer">
                                                Daftarkan Sekarang
                                            </a>
                                        </div>
                                    @endif
                                    
                                    <button type="button" 
                                            wire:click="clearScanResult"
                                            class="w-full py-3 border-2 border-stone-900 text-stone-900 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-50 transition-all cursor-pointer">
                                        Scan Barcode Lain
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari berdasarkan nama atau SKU..." 
                           class="block w-full pl-11 pr-3 py-3 border-none bg-stone-100 rounded-2xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 placeholder-stone-400 text-sm">
                </div>
                
                <div class="relative">
                    <select wire:model.live="categoryFilter" 
                            class="block w-full py-3 border-none bg-stone-100 rounded-2xl focus:ring-2 focus:ring-stone-900 focus:bg-white transition-all duration-200 text-stone-900 appearance-none text-sm cursor-pointer">
                        <option value="">Semua Kategori</option>
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
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-900/10 text-stone-500 backdrop-blur-md border border-white/20 shadow-sm uppercase tracking-widest leading-none">
                                    Nonaktif
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
                                <a href="{{ route('products.edit', $product->id) }}" class="p-2 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition-all" title="Edit Produk">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.138 2.362a2.25 2.25 0 013.182 3.182L9 16.5l-4 1 1-4 10.138-10.138z"/></svg>
                                </a>
                                <button type="button"
                                        wire:click="deleteProduct({{ $product->id }})" 
                                        wire:confirm="Apakah Anda yakin ingin menghapus produk ini?"
                                        class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Hapus Produk">
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
                        <p class="text-stone-500 font-bold">Tidak ada produk ditemukan.</p>
                        <button wire:click="$set('search', ''); $set('categoryFilter', null);" class="mt-2 text-xs font-bold text-stone-900 border-b-2 border-stone-900 cursor-pointer uppercase tracking-widest pb-0.5">Hapus filter</button>
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
