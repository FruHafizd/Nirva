<div class="min-h-screen bg-stone-100 p-4 lg:p-6 font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 max-w-[1600px] mx-auto">
        
        <!-- BAGIAN KIRI: SEARCH & PRODUK -->
        <div class="lg:col-span-8 flex flex-col space-y-6">
            
            <!-- Pencarian & Header -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-200 flex items-center space-x-4">
                <div class="flex-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama produk, SKU, atau scan barcode..." 
                        class="w-full pl-10 pr-4 py-3 bg-stone-50 border-stone-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 transition-all text-stone-700"
                    >
                </div>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-[calc(100vh-220px)] pr-2">
                @forelse($products as $product)
                    <button 
                        wire:click="addToCart({{ $product->id }})"
                        @disabled($product->stock <= 0)
                        class="group relative bg-white border border-stone-200 rounded-2xl p-4 transition-all hover:border-amber-500 hover:shadow-md text-left flex flex-col space-y-3 {{ $product->stock <= 0 ? 'opacity-60 grayscale cursor-not-allowed' : '' }}"
                    >
                        <div class="aspect-square w-full rounded-xl bg-stone-50 overflow-hidden flex items-center justify-center relative">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            @else
                                <svg class="w-12 h-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif

                            @if($product->stock <= 5 && $product->stock > 0)
                                <span class="absolute top-2 right-2 bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Stok Tipis: {{ $product->stock }}</span>
                            @elseif($product->stock <= 0)
                                <span class="absolute top-2 right-2 bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Habis</span>
                            @endif
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="text-xs text-stone-400 font-medium uppercase tracking-tighter">{{ $product->sku ?? 'NO SKU' }}</span>
                            <h3 class="text-stone-800 font-bold text-sm leading-tight line-clamp-2 h-10">{{ $product->name }}</h3>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-stone-50">
                            <span class="text-amber-600 font-black text-base">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-stone-400 font-medium">{{ $product->stock }} {{ $product->unit }}</span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-stone-400">
                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-stone-500 font-medium">Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- BAGIAN KANAN: KERANJANG & CHECKOUT -->
        <div class="lg:col-span-4 flex flex-col h-full space-y-6">
            
            <div class="bg-white rounded-3xl shadow-xl border border-stone-200 flex flex-col h-[calc(100vh-80px)] sticky top-6">
                <!-- Header Keranjang -->
                <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                    <h2 class="text-lg font-black text-stone-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Keranjang
                    </h2>
                    <span class="bg-stone-100 text-stone-600 text-xs font-bold px-3 py-1 rounded-full">{{ count($cart) }} Items</span>
                </div>

                <!-- Daftar Item -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4 font-['Jost'] scrollbar-hide">
                    @forelse($cart as $index => $item)
                        <div class="group flex items-center space-x-3 bg-stone-50/80 p-4 rounded-2xl border border-transparent hover:border-amber-200 hover:shadow-sm transition-all duration-300">
                            <div class="flex-1 overflow-hidden">
                                <h4 class="text-sm font-bold text-stone-800 truncate tracking-tight">{{ $item['name'] }}</h4>
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex items-center bg-white rounded-xl border border-stone-100 p-1 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-stone-900 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="text-xs font-black text-stone-800 min-w-[24px] text-center">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-stone-900 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        </button>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-stone-400 font-bold uppercase tracking-widest block leading-none mb-1">Total</span>
                                        <span class="text-sm font-['Bodoni_Moda'] font-black text-stone-900 italic">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="removeFromCart({{ $index }})" class="p-2 text-stone-300 hover:text-red-500 hover:bg-white rounded-full transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center px-4 space-y-4 opacity-50">
                            <div class="w-24 h-24 bg-stone-50 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-stone-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-stone-800 font-bold tracking-widest text-xs uppercase mb-1 font-['Jost']">Belum ada koleksi</h3>
                                <p class="text-stone-400 text-[10px] font-medium leading-relaxed max-w-[1600px]">Pilih produk di galeri sebelah kiri untuk memulai transaksi Anda.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Perhitungan & Checkout -->
                <div class="p-6 bg-white border-t border-stone-100 rounded-b-3xl text-stone-900 space-y-4">
                    <!-- Kalkulasi Ringkas -->
                    <div class="space-y-2 text-sm font-['Jost']">
                        <div class="flex justify-between text-stone-500 font-medium">
                            <span>Subtotal</span>
                            <span class="text-stone-900 font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-stone-500 font-medium">Pajak (%)</span>
                            <input type="number" wire:model.blur="taxRate" class="w-16 h-8 bg-stone-50 border border-stone-200 rounded-xl text-xs text-stone-900 text-center focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div class="flex justify-between text-stone-500 font-medium tracking-tight">
                            <span>Nominal Pajak</span>
                            <span class="text-stone-900 font-bold">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-2 border-t border-stone-100 flex justify-between items-center">
                            <span class="text-xs font-black text-stone-400 uppercase tracking-widest">Grand Total</span>
                            <span class="text-2xl font-['Bodoni_Moda'] font-black text-amber-600">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Input Pembayaran -->
                    <div class="space-y-4 pt-4 border-t border-stone-100">
                        <div class="grid grid-cols-2 gap-3 font-['Jost']">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Metode Bayar</label>
                                <select wire:model.live="paymentMethod" class="w-full bg-stone-50 border border-stone-200 rounded-xl text-xs text-stone-800 focus:ring-amber-500 py-3 shadow-sm">
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                    <option value="debit">Debit / EDC</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Diterima</label>
                                <input type="number" wire:model.live.debounce.500ms="amountPaid" class="w-full bg-stone-50 border border-stone-200 rounded-xl text-lg font-['Bodoni_Moda'] font-black text-stone-900 focus:ring-amber-500 py-2.5 text-right shadow-sm" placeholder="0">
                            </div>
                        </div>

                        @if($amountPaid > $grandTotal)
                        <div class="flex justify-between items-center py-3 px-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Kembalian</span>
                            <span class="text-xl font-['Bodoni_Moda'] font-black text-amber-600">Rp {{ number_format(max(0, $changeAmount), 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <button 
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            @disabled(count($cart) === 0 || $amountPaid < $grandTotal)
                            class="w-full bg-stone-900 hover:bg-stone-800 disabled:bg-stone-100 disabled:text-stone-300 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-stone-900/20 flex items-center justify-center space-x-2 font-['Jost'] tracking-widest text-xs uppercase"
                        >
                            <span wire:loading.remove>SELESAIKAN TRANSAKSI</span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                MEMPROSES...
                            </span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- NOTIFIKASI -->
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4"
        class="fixed bottom-6 left-6 z-50 px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 pointer-events-none"
        :class="type === 'success' ? 'bg-stone-950 text-stone-50 border border-stone-800' : 'bg-red-500 text-white'"
        style="display: none;"
    >
        <template x-if="type === 'success'">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <template x-if="type === 'error'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <span x-text="message" class="font-bold tracking-tight"></span>
    </div>

    @if(session('success'))
        <script>window.dispatchEvent(new CustomEvent('notify', { detail: { message: "{{ session('success') }}", type: 'success' } }));</script>
    @endif
    
    @if(session('error'))
        <script>window.dispatchEvent(new CustomEvent('notify', { detail: { message: "{{ session('error') }}", type: 'error' } }));</script>
    @endif
</div>
