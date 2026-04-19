<?php

use App\Models\Product;
use Livewire\Volt\Component;

new class extends Component
{
    public string $scannedBarcode = '';
    public bool $isScanning = false;
    public bool $showResult = false;
    public ?Product $foundProduct = null;
    public string $errorMessage = '';
    public string $scanMode = 'device'; // 'device' | 'camera' | 'manual'

    /**
     * Cari produk berdasarkan barcode yang di-scan.
     */
    public function processBarcode(string $barcode): void
    {
        $this->scannedBarcode = trim($barcode);
        $this->isScanning = false;
        $this->errorMessage = '';

        if (empty($this->scannedBarcode)) {
            $this->errorMessage = 'Barcode tidak boleh kosong.';
            return;
        }

        try {
            $this->foundProduct = Product::where('barcode', $this->scannedBarcode)->first();
            $this->showResult = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan saat mencari produk.';
        }
    }

    /**
     * Reset scanner untuk pemindaian ulang.
     */
    public function resetScanner(): void
    {
        $this->scannedBarcode = '';
        $this->foundProduct = null;
        $this->showResult = false;
        $this->isScanning = false;
        $this->errorMessage = '';

        $this->dispatch('scanner-reset');
    }

    /**
     * Ganti mode scan.
     */
    public function switchMode(string $mode): void
    {
        $this->scanMode = $mode;
        $this->resetScanner();
    }
}; ?>

<div class="space-y-5"
     x-data="{
        html5QrCode: null,
        scanning: false,
        cameraError: '',
        
        // === Hardware Scanner (USB/Bluetooth) ===
        scanBuffer: '',
        scanTimeout: null,

        init() {
            Livewire.on('scanner-reset', () => {
                this.cameraError = '';
                this.scanBuffer = '';
            });

            // Global keyboard listener untuk hardware barcode scanner
            // Scanner USB mengirim karakter sangat cepat (< 50ms antar karakter)
            // dan diakhiri dengan Enter key
            document.addEventListener('keydown', (e) => {
                // Hanya aktif di mode 'device'
                if ($wire.get('scanMode') !== 'device') return;
                // Abaikan jika user sedang focus di input lain
                const activeTag = document.activeElement?.tagName?.toLowerCase();
                if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') return;

                if (e.key === 'Enter' && this.scanBuffer.length >= 4) {
                    // Barcode selesai di-scan oleh hardware
                    e.preventDefault();
                    const barcode = this.scanBuffer;
                    this.scanBuffer = '';
                    clearTimeout(this.scanTimeout);
                    $wire.processBarcode(barcode);
                    if (navigator.vibrate) navigator.vibrate(100);
                } else if (e.key.length === 1) {
                    // Kumpulkan karakter
                    this.scanBuffer += e.key;
                    clearTimeout(this.scanTimeout);
                    // Reset buffer jika tidak ada input selama 100ms (bukan scanner)
                    this.scanTimeout = setTimeout(() => {
                        this.scanBuffer = '';
                    }, 100);
                }
            });
        },

        // === Camera Scanner ===
        async startCamera() {
            this.cameraError = '';
            this.scanning = true;
            $wire.set('isScanning', true);
            
            await this.$nextTick();
            
            try {
                this.html5QrCode = new Html5Qrcode('barcode-reader');
                const config = { 
                    fps: 15, 
                    qrbox: { width: 280, height: 160 },
                    aspectRatio: 1.777778,
                    formatsToSupport: [ 
                        Html5QrcodeSupportedFormats.EAN_13, 
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.EAN_8
                    ]
                };

                const onSuccess = (decodedText) => {
                    this.stopCamera();
                    $wire.processBarcode(decodedText);
                    if (navigator.vibrate) navigator.vibrate(100);
                };
                
                // Coba kamera belakang dulu, fallback ke depan (laptop)
                try {
                    await this.html5QrCode.start({ facingMode: 'environment' }, config, onSuccess);
                } catch {
                    await this.html5QrCode.start({ facingMode: 'user' }, config, onSuccess);
                }
            } catch (err) {
                console.error('Camera Error:', err);
                this.scanning = false;
                $wire.set('isScanning', false);
                this.cameraError = 'Kamera tidak dapat diakses. Pastikan izin kamera diberikan.';
            }
        },

        async stopCamera() {
            if (this.html5QrCode && this.scanning) {
                try {
                    await this.html5QrCode.stop();
                    await this.html5QrCode.clear();
                } catch (err) {
                    console.error('Stop Error:', err);
                }
            }
            this.scanning = false;
            $wire.set('isScanning', false);
        }
     }"
     x-init="init()"
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
            <div id="barcode-reader" class="w-full h-full" x-show="scanning"></div>
            
            <template x-if="!scanning && !$wire.showResult">
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

    <!-- ==================== RESULTS AREA (Shared) ==================== -->
    @if($showResult)
        <div class="space-y-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
            @if($foundProduct)
                <div class="p-6 bg-stone-900 rounded-2xl shadow-xl shadow-stone-200 text-white flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] uppercase tracking-widest text-stone-400 font-bold mb-0.5">Produk Ditemukan</p>
                        <h5 class="font-bold truncate">{{ $foundProduct->name }}</h5>
                        <p class="text-xs text-stone-300">{{ $foundProduct->barcode }} • {{ $foundProduct->formatted_price }}</p>
                    </div>
                    <a href="{{ route('products.edit', $foundProduct) }}" 
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
                            <p class="text-xs text-stone-500 mt-1">Produk dengan barcode <span class="font-mono font-bold text-stone-900 underline">{{ $scannedBarcode }}</span> belum terdaftar di sistem.</p>
                        </div>
                    </div>
                    <a href="{{ route('products.create', ['barcode' => $scannedBarcode]) }}" 
                       wire:navigate
                       class="flex-1 inline-flex items-center justify-center w-full px-4 py-2.5 bg-stone-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-800 transition-all cursor-pointer">
                        Daftarkan Sekarang
                    </a>
                </div>
            @endif
            
            <button type="button" 
                    wire:click="resetScanner"
                    class="w-full py-3 border-2 border-stone-900 text-stone-900 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-50 transition-all cursor-pointer">
                Scan Barcode Lain
            </button>
        </div>
    @endif

    <!-- Error Message -->
    @if($errorMessage)
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-bold text-red-700">{{ $errorMessage }}</p>
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
    </style>
</div>
