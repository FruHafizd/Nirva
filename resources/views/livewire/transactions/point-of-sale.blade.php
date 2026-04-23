<div class="min-h-screen bg-stone-50 p-4 lg:p-8 font-['Fira_Sans']" x-data="{ showScanner: false }">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-[1600px] mx-auto h-full">
        
        <!-- BAGIAN KIRI: PRODUK -->
        <div class="lg:col-span-8 flex flex-col space-y-8">
            
            <!-- Pencarian & Kontrol Utama -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-stone-200 flex items-center gap-4">
                <div class="flex-1 relative group">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-stone-400 group-focus-within:text-blue-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama produk, kode SKU, atau scan barang..." 
                        class="w-full pl-14 pr-6 py-4 bg-stone-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all text-lg font-medium text-stone-700 placeholder-stone-400"
                    >
                </div>
                
                <button @click="showScanner = true" 
                        class="p-4 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center gap-3 font-bold group">
                    <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="hidden md:inline font-['Fira_Code'] uppercase tracking-wider text-xs">Scan Barang</span>
                </button>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 overflow-y-auto max-h-[calc(100vh-220px)] pr-2 custom-scrollbar">
                @forelse($products as $product)
                    <div 
                        wire:click="addToCart({{ $product->id }})"
                        class="group relative bg-white border-2 border-transparent rounded-[2rem] p-5 transition-all hover:border-blue-500 hover:shadow-2xl hover:shadow-blue-100 cursor-pointer flex flex-col space-y-4 shadow-sm {{ $product->stock <= 0 ? 'opacity-50 grayscale cursor-not-allowed' : '' }}"
                    >
                        <div class="aspect-square w-full rounded-2xl bg-stone-50 overflow-hidden relative">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-stone-200">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            <div class="absolute top-3 right-3 flex flex-col gap-2">
                                @if($product->stock <= 5 && $product->stock > 0)
                                    <span class="bg-amber-500 text-white text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest shadow-lg font-['Fira_Code']">Limit: {{ $product->stock }}</span>
                                @elseif($product->stock <= 0)
                                    <span class="bg-stone-800 text-white text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest shadow-lg font-['Fira_Code']">Habis</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex flex-col flex-1">
                            <h3 class="text-stone-900 font-bold text-base leading-snug line-clamp-2 h-12 group-hover:text-blue-600 transition-colors">{{ $product->name }}</h3>
                            <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest mt-1 font-['Fira_Code']">{{ $product->sku ?? 'TANPA SKU' }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-stone-50">
                            <span class="text-blue-600 font-black text-lg font-['Fira_Code']">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                            <div class="flex items-center gap-1.5 bg-stone-100 px-3 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full {{ $product->stock > 5 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                <span class="text-[10px] text-stone-500 font-bold uppercase font-['Fira_Code']">{{ $product->stock }} {{ $product->unit }}</span>
                            </div>
                        </div>

                        <!-- Hover Overlay Button -->
                        <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/5 transition-all rounded-[2rem] flex items-center justify-center opacity-0 group-hover:opacity-100">
                             <div class="bg-blue-600 text-white p-4 rounded-full shadow-2xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                             </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-32 flex flex-col items-center justify-center text-stone-300">
                        <div class="w-32 h-32 bg-stone-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-stone-500 font-bold text-lg uppercase tracking-widest font-['Fira_Code']">Produk Tidak Ditemukan</p>
                        <p class="text-stone-400 text-sm mt-2">Coba kata kunci lain atau scan barcode produk</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- BAGIAN KANAN: KERANJANG -->
        <div class="lg:col-span-4 flex flex-col h-full">
            
            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-stone-200/50 border border-stone-200 flex flex-col h-[calc(100vh-80px)] sticky top-8 overflow-hidden">
                <!-- Header Keranjang -->
                <div class="p-8 border-b border-stone-100 flex items-center justify-between bg-stone-50/50 shrink-0">
                    <div>
                        <h2 class="text-2xl font-black text-stone-900 flex items-center gap-3">
                            <div class="p-2 bg-blue-600 text-white rounded-xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            Pesanan
                        </h2>
                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest mt-2 font-['Fira_Code']">Kasir: {{ auth()->user()->name }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-700 text-xs font-black px-4 py-2 rounded-xl border border-blue-200 font-['Fira_Code']">{{ count($cart) }} ITEM</span>
                </div>

                <!-- Daftar Item -->
                <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
                    @forelse($cart as $index => $item)
                        <div class="group flex items-center gap-6 bg-stone-50/50 p-5 rounded-3xl border border-transparent hover:bg-white hover:border-blue-100 hover:shadow-xl hover:shadow-blue-50/50 transition-all duration-300 relative">
                            <div class="flex-1 overflow-hidden">
                                <h4 class="text-base font-bold text-stone-900 truncate">{{ $item['name'] }}</h4>
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center bg-white rounded-2xl border-2 border-stone-100 p-1.5 shadow-sm">
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="w-8 h-8 flex items-center justify-center text-stone-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="text-sm font-black text-stone-900 min-w-[40px] text-center font-['Fira_Code']">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="w-8 h-8 flex items-center justify-center text-stone-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        </button>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest leading-none mb-1 font-['Fira_Code']">Total</p>
                                        <p class="text-lg font-black text-stone-900 font-['Fira_Code']">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="removeFromCart({{ $index }})" class="p-3 text-stone-300 hover:text-rose-600 hover:bg-rose-50 rounded-2xl transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center px-10 space-y-6 opacity-40">
                            <div class="w-24 h-24 bg-stone-100 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-stone-900 font-bold tracking-widest text-xs uppercase mb-2">Keranjang Kosong</h3>
                                <p class="text-stone-500 text-xs leading-relaxed">Pilih produk di sebelah kiri atau gunakan scanner untuk memulai transaksi.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Perhitungan & Checkout -->
                <div class="p-8 bg-stone-900 text-white rounded-t-[3rem] shrink-0 space-y-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-stone-400 text-sm font-bold">
                            <span class="uppercase tracking-widest font-['Fira_Code'] text-[10px]">Subtotal</span>
                            <span class="font-['Fira_Code'] text-sm">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-stone-400 text-sm font-bold">
                            <span class="uppercase tracking-widest font-['Fira_Code'] text-[10px]">Pajak (%)</span>
                            <div class="flex items-center gap-2">
                                <input type="number" wire:model.blur="taxRate" class="w-16 h-10 bg-white/10 border-transparent rounded-xl text-sm text-white text-center focus:ring-2 focus:ring-blue-500 font-['Fira_Code']">
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-white/10">
                            <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest font-['Fira_Code']">Total Bayar</span>
                            <span class="text-4xl font-black text-white font-['Fira_Code'] tracking-tighter">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest font-['Fira_Code']">Metode</label>
                            <select wire:model.live="paymentMethod" class="w-full bg-white/10 border-transparent rounded-2xl text-xs text-white font-bold focus:ring-2 focus:ring-blue-500 py-4 cursor-pointer font-['Fira_Code'] uppercase">
                                <option value="cash" class="text-stone-900">TUNAI</option>
                                <option value="qris" class="text-stone-900">QRIS</option>
                                <option value="debit" class="text-stone-900">DEBIT / EDC</option>
                                <option value="transfer" class="text-stone-900">TRANSFER</option>
                            </select>
                        </div>
                        <div class="space-y-2 text-right">
                            <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest font-['Fira_Code']">Diterima</label>
                            <input type="number" wire:model.live.debounce.500ms="amountPaid" class="w-full bg-white/10 border-transparent rounded-2xl text-xl font-black text-white focus:ring-2 focus:ring-blue-500 py-3 text-right font-['Fira_Code']" placeholder="0">
                        </div>
                    </div>

                    @if($amountPaid > $grandTotal)
                    <div class="flex justify-between items-center py-4 px-6 bg-amber-500 rounded-3xl animate-in fade-in slide-in-from-bottom-2">
                        <span class="text-[10px] font-black text-amber-100 uppercase tracking-widest font-['Fira_Code']">Kembalian</span>
                        <span class="text-2xl font-black text-white font-['Fira_Code']">Rp{{ number_format(max(0, $changeAmount), 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <button 
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        @disabled(count($cart) === 0 || $amountPaid < $grandTotal)
                        class="w-full bg-amber-500 hover:bg-amber-600 disabled:bg-stone-800 disabled:text-stone-600 text-white font-black py-5 rounded-[2rem] transition-all shadow-2xl shadow-amber-500/20 flex items-center justify-center gap-3 tracking-widest text-xs uppercase font-['Fira_Code']"
                    >
                        <span wire:loading.remove>Selesaikan Pesanan</span>
                        <span wire:loading class="flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            MEMPROSES...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal Overlay -->
    <div x-show="showScanner" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-stone-900/80 backdrop-blur-md" 
         @keydown.escape.window="showScanner = false">
        
        <div class="bg-white rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-stone-200" 
             @click.away="showScanner = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-12"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-10 py-8 flex items-center justify-between border-b border-stone-100 bg-stone-50/50">
                <div>
                    <h3 class="text-2xl font-black text-stone-900">Scanner Barcode</h3>
                    <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest mt-1 font-['Fira_Code']">Arahkan barcode ke kamera</p>
                </div>
                <button @click="showScanner = false" class="p-3 text-stone-400 hover:text-rose-600 hover:bg-rose-50 rounded-2xl transition-all">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-10">
                <livewire:products.barcode-scanner />
            </div>
        </div>
    </div>

    <!-- NOTIFIKASI -->
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-10"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-10"
        class="fixed bottom-10 right-10 z-[110] px-8 py-5 rounded-[2rem] shadow-2xl flex items-center gap-4 pointer-events-none"
        :class="type === 'success' ? 'bg-stone-900 text-white border border-stone-800' : 'bg-rose-600 text-white'"
        style="display: none;"
    >
        <template x-if="type === 'success'">
            <div class="p-2 bg-amber-500 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
        </template>
        <template x-if="type === 'error'">
            <div class="p-2 bg-white/20 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </template>
        <span x-text="message" class="font-black tracking-tight text-lg"></span>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
    </style>
</div>
