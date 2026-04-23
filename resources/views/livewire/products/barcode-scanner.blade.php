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

<div class="space-y-5"
     x-data="barcodeScanner({
        elementId: 'barcode-reader-{{ $this->getId() }}',
        onScan: (barcode) => $wire.processBarcode(barcode)
     })"
     x-on:scanner-reset.window="reset()"
     @close-modal.window="stopCamera()">

    <!-- Mode Selector Tabs -->
    <div class="flex rounded-xl bg-stone-100 p-1 gap-1">
        <button type="button"
                wire:click="switchMode('device')"
                @click="stopCamera()"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-all duration-200 cursor-pointer {{ $scanMode === 'device' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
            </svg>
            Scanner
        </button>
        <button type="button"
                wire:click="switchMode('camera')"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-all duration-200 cursor-pointer {{ $scanMode === 'camera' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Kamera
        </button>
        <button type="button"
                wire:click="switchMode('manual')"
                @click="stopCamera()"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-all duration-200 cursor-pointer {{ $scanMode === 'manual' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-400 hover:text-stone-600' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Manual
        </button>
    </div>

    <!-- ==================== MODE: DEVICE (USB/Bluetooth Scanner) ==================== -->
    @if($scanMode === 'device')
        <div class="rounded-2xl bg-stone-50 border-2 border-dashed border-stone-200 p-8 text-center">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-5 shadow-sm border border-stone-100">
                <svg class="w-10 h-10 text-stone-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <h4 class="text-stone-900 font-bold text-base mb-2">Menunggu Scanner...</h4>
            <p class="text-xs text-stone-500 max-w-[280px] mx-auto leading-relaxed">
                Langsung pindai barcode menggunakan alat scanner USB atau Bluetooth Anda.
                Hasil akan muncul secara <span class="font-bold text-stone-900">instan</span>.
            </p>
            <div class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full border border-stone-200 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Siap Menerima Input</span>
            </div>
        </div>
    @endif

    <!-- ==================== MODE: CAMERA ==================== -->
    @if($scanMode === 'camera')
        <div class="relative overflow-hidden rounded-2xl bg-stone-100 aspect-video border-2 border-dashed border-stone-200 flex flex-col items-center justify-center group">
            <div id="barcode-reader-{{ $this->getId() }}" class="w-full h-full" x-show="scanning"></div>
            
            <template x-if="!scanning">
                <div class="text-center p-8">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-stone-100 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-stone-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-stone-900 font-bold mb-1">Scan via Kamera</h4>
                    <p class="text-xs text-stone-500 max-w-[220px] mx-auto mb-6">Kamera akan digunakan untuk mendeteksi barcode. Kecepatan tergantung kualitas kamera.</p>
                    <button type="button" 
                            @click="startCamera()"
                            class="inline-flex items-center px-6 py-2.5 bg-stone-900 text-white rounded-xl font-bold text-sm hover:bg-stone-800 transition-all shadow-lg shadow-stone-200 cursor-pointer">
                        Mulai Kamera
                    </button>
                </div>
            </template>

            <!-- Scanning Animation Overlay -->
            <template x-if="scanning">
                <div class="absolute inset-x-0 top-0 h-1 bg-stone-900/50 shadow-[0_0_15px_rgba(28,25,23,0.5)] animate-scan z-10"></div>
            </template>

            <!-- Ganti Kamera Button -->
            <button type="button"
                    x-show="scanning"
                    @click="switchCamera()"
                    class="absolute bottom-4 right-4 z-20 bg-white/20 backdrop-blur-md hover:bg-white/40 text-white p-3 rounded-full transition-all border border-white/30"
                    title="Ganti Kamera">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>

            <!-- Camera Label -->
            <div x-show="scanning" 
                 class="absolute top-4 left-4 z-20 px-3 py-1 bg-black/40 backdrop-blur-md text-white text-[10px] font-medium rounded-full border border-white/10 uppercase tracking-widest"
                 x-text="cameraLabel">
            </div>
            
            <!-- Success Flash Overlay -->
            <div x-show="scanSuccess" 
                 x-transition:enter="transition ease-out duration-150" 
                 x-transition:leave="transition ease-in duration-300" 
                 class="absolute inset-0 bg-green-500/20 z-30 flex items-center justify-center backdrop-blur-[2px]">
                <div class="bg-white rounded-full p-4 shadow-2xl scale-110">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <!-- Camera Error State -->
            <template x-if="cameraError">
                <div class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center z-20">
                    <svg class="w-12 h-12 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm font-bold text-stone-900 mb-2">Akses Kamera Gagal</p>
                    <p class="text-xs text-stone-500 mb-6" x-text="cameraError"></p>
                    <button @click="cameraError = ''" class="text-xs font-bold text-stone-900 underline uppercase tracking-widest cursor-pointer">Tutup</button>
                </div>
            </template>
        </div>

        <!-- Stop Camera Button -->
        <template x-if="scanning">
            <button type="button" @click="stopCamera()" 
                    class="w-full py-3 border-2 border-red-200 text-red-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-50 transition-all cursor-pointer">
                Hentikan Kamera
            </button>
        </template>
    @endif

    <!-- ==================== MODE: MANUAL ==================== -->
    @if($scanMode === 'manual')
        <div class="space-y-3">
            <div class="rounded-2xl bg-stone-50 border border-stone-200 p-6">
                <label for="manual_barcode_input" class="block text-[10px] uppercase tracking-widest text-stone-400 font-bold mb-3">Masukkan Barcode</label>
                <div class="relative group">
                    <input type="text" 
                           id="manual_barcode_input"
                           wire:model="scannedBarcode"
                           placeholder="Ketik atau tempel angka barcode..."
                           autofocus
                           class="w-full bg-white border-stone-200 rounded-xl py-3.5 pl-4 pr-24 text-sm font-mono focus:ring-stone-900 focus:border-stone-900 transition-all"
                           @keydown.enter="$wire.processBarcode($el.value)">
                    <button type="button"
                            @click="$wire.processBarcode($wire.get('scannedBarcode'))"
                            class="absolute right-2 top-2 bottom-2 px-5 bg-stone-900 text-white rounded-lg font-bold text-[10px] uppercase tracking-widest hover:bg-stone-800 transition-all cursor-pointer">
                        Cari
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes scan {
            0% { top: 0; }
            50% { top: 95%; }
            100% { top: 0; }
        }
        .animate-scan {
            animation: scan 2s linear infinite;
        }
        
        /* Optimasi visual untuk decoder */
        [id^="barcode-reader-"] video {
            object-fit: cover !important;
            filter: contrast(1.15) brightness(1.05);
        }
    </style>
</div>
