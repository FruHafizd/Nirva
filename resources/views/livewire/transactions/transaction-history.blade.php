<div class="min-h-screen bg-stone-100 p-6 font-sans">
    <div class="max-w-[1400px] mx-auto space-y-6">
        
        <!-- HEADER & FILTER -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-stone-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-stone-800 tracking-tight">Riwayat Transaksi</h1>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-widest mt-1">Kelola dan monitor semua aktivitas penjualan</p>
                </div>
                
                <a href="{{ route('transactions.pos') }}" class="inline-flex items-center bg-amber-500 hover:bg-amber-600 text-stone-950 font-black text-xs px-6 py-3 rounded-2xl transition-all shadow-lg shadow-amber-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    TRANSAKSI BARU
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor invoice / pelanggan..." class="w-full pl-10 pr-4 py-2.5 bg-stone-50 border-stone-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500">
                </div>
                <select wire:model.live="status" class="bg-stone-50 border-stone-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500 py-2.5">
                    <option value="">Semua Status</option>
                    <option value="completed">Completed</option>
                    <option value="voided">Voided / Batal</option>
                </select>
                <input type="date" wire:model.live="dateFrom" class="bg-stone-50 border-stone-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500 py-2.5">
                <input type="date" wire:model.live="dateTo" class="bg-stone-50 border-stone-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500 py-2.5">
            </div>
        </div>

        <!-- TABEL -->
        <div class="bg-white rounded-3xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-100">
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest">No. Invoice</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest text-center">Items</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest">Metode</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-stone-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-stone-800">{{ $transaction->invoice_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-stone-700">{{ $transaction->transaction_date->format('d M Y') }}</span>
                                        <span class="text-[10px] text-stone-400">{{ $transaction->transaction_date->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-stone-600">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center bg-stone-100 text-stone-600 text-[10px] font-black w-6 h-6 rounded-full">{{ $transaction->items_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black text-stone-900 italic">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold uppercase text-stone-500">{{ $transaction->payment_method }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($transaction->status === 'completed')
                                        <span class="inline-flex px-3 py-1 text-[10px] font-black bg-stone-950 text-stone-50 rounded-full uppercase tracking-tighter italic">SELESAI</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 text-[10px] font-black bg-red-100 text-red-600 rounded-full uppercase tracking-tighter italic">DIBATALKAN</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="showDetail({{ $transaction->id }})" class="p-2 text-stone-400 hover:text-amber-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-20 text-center text-stone-400 flex flex-col items-center">
                                    <svg class="w-16 h-16 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="font-bold">Belum ada transaksi</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-stone-50 bg-stone-50/30">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DETAIL -->
    @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-stone-900/60 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Detail Transaksi</span>
                                <h3 class="text-2xl font-black text-stone-800">{{ $selectedTransaction->invoice_number }}</h3>
                                <p class="text-xs text-stone-500 font-medium">{{ $selectedTransaction->transaction_date->format('d F Y - H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($selectedTransaction->status === 'completed')
                                    <span class="bg-stone-950 text-stone-50 text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-tighter">SUCCESS</span>
                                @else
                                    <span class="bg-red-500 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-tighter">VOIDED</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Informasi Customer & Kasir -->
                            <div class="grid grid-cols-2 gap-8 py-6 border-y border-stone-50">
                                <div>
                                    <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Pelanggan</p>
                                    <p class="text-sm font-bold text-stone-800">{{ $selectedTransaction->customer->name ?? 'Walk-in Customer' }}</p>
                                    @if($selectedTransaction->customer?->phone)
                                        <p class="text-[10px] text-stone-500 uppercase tracking-tighter mt-1">{{ $selectedTransaction->customer->phone }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Kasir</p>
                                    <p class="text-sm font-bold text-stone-800">{{ $selectedTransaction->user->name }}</p>
                                    <p class="text-[10px] text-stone-500 uppercase tracking-tighter mt-1">{{ $selectedTransaction->user->email }}</p>
                                </div>
                            </div>

                            <!-- Daftar Item -->
                            <table class="w-full text-left">
                                <thead class="border-b border-stone-50">
                                    <tr>
                                        <th class="py-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">Produk</th>
                                        <th class="py-2 text-[10px] font-black text-stone-400 uppercase tracking-widest text-center">Qty</th>
                                        <th class="py-2 text-[10px] font-black text-stone-400 uppercase tracking-widest text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-50">
                                    @foreach($selectedTransaction->items as $item)
                                        <tr>
                                            <td class="py-3">
                                                <p class="text-xs font-bold text-stone-800">{{ $item->product_name }}</p>
                                                <p class="text-[9px] text-stone-400 uppercase">{{ $item->product_sku }} @ Rp {{ number_format($item->unit_price,0,',','.') }}</p>
                                            </td>
                                            <td class="py-3 text-center">
                                                <span class="text-xs font-medium text-stone-600">{{ $item->quantity }}</span>
                                            </td>
                                            <td class="py-3 text-right">
                                                <span class="text-xs font-black text-stone-800 italic">Rp {{ number_format($item->subtotal,0,',','.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Kalkulasi Akhir -->
                            <div class="bg-stone-50 p-6 rounded-2xl space-y-2">
                                <div class="flex justify-between text-xs text-stone-500 font-medium tracking-tight">
                                    <span>Subtotal Belanja</span>
                                    <span>Rp {{ number_format($selectedTransaction->subtotal,0,',','.') }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-stone-500 font-medium tracking-tight">
                                    <span>Pajak ({{ number_format($selectedTransaction->tax_rate,0) }}%)</span>
                                    <span>Rp {{ number_format($selectedTransaction->tax_amount,0,',','.') }}</span>
                                </div>
                                <div class="flex justify-between pt-3 border-t border-stone-200">
                                    <span class="text-sm font-black text-stone-800 uppercase tracking-widest">Grand Total</span>
                                    <span class="text-lg font-black text-stone-950 italic">Rp {{ number_format($selectedTransaction->grand_total,0,',','.') }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-stone-400 font-medium pt-2 italic">
                                    <span>Metode Bayar: {{ strtoupper($selectedTransaction->payment_method) }}</span>
                                    <span>Diterima: Rp {{ number_format($selectedTransaction->amount_paid,0,',','.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="mt-8 flex justify-between items-center bg-stone-50 -m-8 p-8 rounded-b-3xl">
                            @if($selectedTransaction->status === 'completed')
                                <button 
                                    wire:click="voidTransaction({{ $selectedTransaction->id }})" 
                                    wire:confirm="Apakah Anda yakin ingin membatalkan transaksi ini? Stok akan dikembalikan."
                                    class="text-red-500 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 px-4 py-2 rounded-xl transition-all"
                                >
                                    Batalkan Transaksi
                                </button>
                            @else
                                <div></div>
                            @endif
                            <button wire:click="$set('showDetailModal', false)" class="bg-stone-900 text-stone-50 font-black text-xs px-8 py-3 rounded-2xl">
                                TUTUP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
