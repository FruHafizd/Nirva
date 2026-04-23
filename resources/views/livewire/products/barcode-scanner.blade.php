<?php

use App\Models\Product;
use Livewire\Volt\Component;

new class extends Component
{
    public string $scannedBarcode = '';
    public string $scanMode = 'device'; // 'device' | 'camera' | 'manual'

    /**
     * Proses barcode — dispatch event dengan data product (jika ada).
     * Parent component yang menentukan action selanjutnya.
     */
    public function processBarcode(string $barcode): void
    {
        $this->scannedBarcode = trim($barcode);
        if (empty($this->scannedBarcode)) return;

        $product = Product::where('barcode', $this->scannedBarcode)->first();

        // Dispatch event ke parent — parent yang decide mau ngapain
        $this->dispatch('barcode-result', [
            'barcode' => $this->scannedBarcode,
            'found' => (bool) $product,
            'product_id' => $product?->id,
            'product_name' => $product?->name,
            'product_price' => $product?->price,
        ]);
    }

    public function switchMode(string $mode): void
    {
        $this->scanMode = $mode;
        $this->scannedBarcode = '';
        $this->dispatch('scanner-reset');
    }
}; ?>

<div class="space-y-6 font-['Fira_Sans']"
     x-data="barcodeScanner({
        elementId: 'barcode-reader-{{ $this->getId() }}',
        onScan: (barcode) => $wire.processBarcode(barcode)
     })"
     x-on:scanner-reset.window="reset()"
     @close-modal.window="stopCamera()">

    <!-- Mode Selector Tabs -->
    <div class="flex rounded-2xl bg-stone-100 p-1.5 gap-1.5">
        <button type="button"
                wire:click="switchMode('device')"
                @click="stopCamera()"
                class="flex-1 flex items-center justify-center gap-3 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 cursor-pointer {{ $scanMode === 'device' ? 'bg-white text-blue-600 shadow-xl shadow-stone-200/50' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
            </svg>
            Scanner
        </button>
        <button type="button"
                wire:click="switchMode('camera')"
                class="flex-1 flex items-center justify-center gap-3 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 cursor-pointer {{ $scanMode === 'camera' ? 'bg-white text-blue-600 shadow-xl shadow-stone-200/50' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Kamera
        </button>
        <button type="button"
                wire:click="switchMode('manual')"
                @click="stopCamera()"
                class="flex-1 flex items-center justify-center gap-3 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 cursor-pointer {{ $scanMode === 'manual' ? 'bg-white text-blue-600 shadow-xl shadow-stone-200/50' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Manual
        </button>
    </div>

    <!-- ==================== MODE: DEVICE ==================== -->
    @if($scanMode === 'device')
        <div class="rounded-[2.5rem] bg-stone-50 border-2 border-dashed border-stone-200 p-12 text-center">
            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-xl shadow-stone-100 border border-stone-50">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <h4 class="text-stone-900 font-black text-xl mb-3 uppercase tracking-tight font-['Fira_Code']">Menunggu Scanner...</h4>
            <p class="text-sm text-stone-500 max-w-[320px] mx-auto leading-relaxed font-medium">
                Gunakan alat scanner barcode Anda. Sistem akan mendeteksi input secara <span class="text-blue-600 font-bold">otomatis</span>.
            </p>
            <div class="mt-8 inline-flex items-center gap-3 px-6 py-3 bg-white rounded-2xl border border-stone-200 shadow-sm">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-black text-stone-500 uppercase tracking-widest font-['Fira_Code']">Sistem Siap</span>
            </div>
        </div>
    @endif

    <!-- ==================== MODE: CAMERA ==================== -->
    @if($scanMode === 'camera')
        <div class="relative overflow-hidden rounded-[2.5rem] bg-stone-900 aspect-video border-4 border-stone-100 shadow-2xl flex flex-col items-center justify-center group">
            <div id="barcode-reader-{{ $this->getId() }}" class="w-full h-full" x-show="scanning"></div>
            
            <template x-if="!scanning">
                <div class="text-center p-12">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-6 border border-white/20 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-white font-black text-lg mb-2">Pindai via Kamera</h4>
                    <p class="text-xs text-stone-400 max-w-[240px] mx-auto mb-8 font-medium">Arahkan kamera ke barcode produk.</p>
                    <button type="button" 
                            @click="startCamera()"
                            class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-2xl shadow-blue-500/20 cursor-pointer font-['Fira_Code']">
                        Aktifkan Kamera
                    </button>
                </div>
            </template>

            <!-- Scanning Animation Overlay -->
            <template x-if="scanning">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-blue-500 shadow-[0_0_25px_rgba(59,130,246,0.8)] animate-scan z-10"></div>
            </template>

            <!-- Ganti Kamera Button -->
            <button type="button"
                    x-show="scanning"
                    @click="switchCamera()"
                    class="absolute bottom-6 right-6 z-20 bg-white/20 backdrop-blur-md hover:bg-white/40 text-white p-4 rounded-2xl transition-all border border-white/30"
                    title="Ganti Kamera">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            
            <!-- Success Flash Overlay -->
            <div x-show="scanSuccess" 
                 x-transition:enter="transition ease-out duration-150" 
                 x-transition:leave="transition ease-in duration-300" 
                 class="absolute inset-0 bg-emerald-500/40 z-30 flex items-center justify-center backdrop-blur-md">
                <div class="bg-white rounded-full p-6 shadow-2xl scale-125">
                    <svg class="w-16 h-16 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <template x-if="scanning">
            <button type="button" @click="stopCamera()" 
                    class="w-full py-4 bg-rose-50 text-rose-600 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-100 transition-all cursor-pointer border-2 border-rose-100 font-['Fira_Code']">
                Matikan Kamera
            </button>
        </template>
    @endif

    <!-- ==================== MODE: MANUAL ==================== -->
    @if($scanMode === 'manual')
        <div class="bg-stone-50 rounded-[2.5rem] border-2 border-stone-100 p-8">
            <label for="manual_barcode_input" class="block text-[10px] uppercase tracking-widest text-stone-400 font-black mb-4 ml-1 font-['Fira_Code']">Ketik Barcode Produk</label>
            <div class="relative group">
                <input type="text" 
                       id="manual_barcode_input"
                       wire:model="scannedBarcode"
                       placeholder="Contoh: 899123456789..."
                       autofocus
                       class="w-full bg-white border-2 border-stone-100 rounded-2xl py-5 pl-6 pr-32 text-lg font-black text-stone-900 placeholder-stone-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all font-['Fira_Code']"
                       @keydown.enter="$wire.processBarcode($el.value)">
                <button type="button"
                        @click="$wire.processBarcode($wire.get('scannedBarcode'))"
                        class="absolute right-3 top-3 bottom-3 px-6 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 cursor-pointer font-['Fira_Code']">
                    Cari
                </button>
            </div>
        </div>
    @endif

    <style>
        @keyframes scan {
            0% { top: 0; }
            50% { top: 97%; }
            100% { top: 0; }
        }
        .animate-scan {
            animation: scan 2.5s ease-in-out infinite;
        }
    </style>
</div>
