<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
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

            // Auto-fill barcode dari query parameter (untuk fitur scan)
            $this->barcode = request()->query('barcode');
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

        // 1. Handle Photo Cleanup & Upload
        if ($this->photo) {
            // Hapus foto lama jika ada dan bukan URL eksternal
            if ($this->product && $this->product->image_url && str_contains($this->product->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $this->product->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->photo->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        } elseif ($this->image_url === null && $this->product && $this->product->image_url) {
            // Jika foto sengaja dihapus
            if (str_contains($this->product->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $this->product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $data['image_url'] = null;
        }

        // Remove photo from data before saving to DB
        unset($data['photo']);

        if ($this->product) {
            $this->product->update($data);
            $message = 'Produk berhasil diperbarui.';
        } else {
            Product::create($data);
            $message = 'Produk berhasil ditambahkan.';
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

<div class="max-w-full px-4 sm:px-6 lg:px-12 py-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <form wire:submit="save" class="space-y-12">
        <!-- Persistent Header Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-stone-200 pb-8">
            <div>
                <h1 class="text-3xl font-black text-stone-900 tracking-tight">
                    {{ $product ? 'Edit Produk' : 'Tambah Produk' }}
                </h1>
                <p class="text-stone-500 mt-2 text-lg">
                    {{ $product ? "Mengelola detail untuk item {$product->sku}" : "Lengkapi informasi produk untuk inventaris baru." }}
                </p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('products.index') }}" 
                   wire:navigate
                   class="px-6 py-3 text-sm font-bold text-stone-400 hover:text-stone-900 transition-all uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-8 py-4 bg-stone-900 text-white rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-stone-800 focus:ring-4 focus:ring-stone-200 transition-all shadow-2xl shadow-stone-200 group">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        {{ $product ? 'Perbarui Produk' : 'Simpan Produk' }}
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-8 space-y-10">
                <!-- General Section -->
                <section class="bg-white/40 backdrop-blur-sm border border-stone-200 rounded-[2.5rem] p-8 md:p-10 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-full bg-stone-900 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-stone-900 tracking-tight">Informasi Umum</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="name" :value="__('Nama Produk')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                            <x-text-input wire:model="name" id="name" type="text" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all text-lg font-medium shadow-inner" placeholder="Contoh: Kemeja Flanel Slim Fit" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Deskripsi Produk')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                            <textarea wire:model="description" id="description" rows="5" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all text-stone-900 shadow-inner resize-none" placeholder="Ceritakan detail produk Anda..."></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="category_id" :value="__('Kategori')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                                <div class="relative">
                                    <select wire:model="category_id" id="category_id" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all text-stone-900 appearance-none shadow-inner">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-stone-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="unit" :value="__('Satuan')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                                <x-text-input wire:model="unit" id="unit" type="text" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all shadow-inner" placeholder="pcs, kg, box, dll" required />
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Pricing Section -->
                <section class="bg-white/40 backdrop-blur-sm border border-stone-200 rounded-[2.5rem] p-8 md:p-10 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-stone-100/50 rounded-bl-full -z-10 transition-all group-hover:scale-110"></div>
                    
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-stone-900 tracking-tight">Harga & Inventaris</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <x-input-label for="price" :value="__('Harga Jual')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-stone-400 font-bold text-lg">Rp</div>
                                <x-text-input wire:model="price" id="price" type="number" step="0.01" class="block w-full pl-14 pr-6 py-4 bg-white border-stone-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-2xl transition-all text-xl font-black text-emerald-600 shadow-inner shadow-emerald-50/50" required />
                            </div>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="cost_price" :value="__('Harga Modal (Opsional)')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                            <div class="relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-stone-400 font-bold text-lg">Rp</div>
                                <x-text-input wire:model="cost_price" id="cost_price" type="number" step="0.01" class="block w-full pl-14 pr-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all text-xl font-bold text-stone-600 shadow-inner" />
                            </div>
                            <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="stock" :value="__('Stok Saat Ini')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                            <div class="flex items-center gap-4">
                                <x-text-input wire:model="stock" id="stock" type="number" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all text-xl font-bold shadow-inner" required />
                                <div class="text-stone-400 font-bold uppercase tracking-widest text-xs">{{ $unit ?: 'UNIT' }}</div>
                            </div>
                            <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column: Media & Sidebar Info -->
            <div class="lg:col-span-4 space-y-10">
                <!-- Visual Identity Section -->
                <section x-data="{ 
                    showCamera: false, 
                    cameraStream: null, 
                    capturedPhoto: null,
                    isUploading: false,
                    
                    async openCamera() {
                        this.showCamera = true;
                        this.capturedPhoto = null;
                        this.isUploading = false;
                        await this.$nextTick();
                        try {
                            this.cameraStream = await navigator.mediaDevices.getUserMedia({
                                video: { 
                                    facingMode: 'environment',
                                    width: { ideal: 1280 },
                                    height: { ideal: 720 }
                                }
                            });
                            this.$refs.cameraPreview.srcObject = this.cameraStream;
                        } catch (err) {
                            try {
                                this.cameraStream = await navigator.mediaDevices.getUserMedia({
                                    video: { 
                                        facingMode: 'user',
                                        width: { ideal: 1280 },
                                        height: { ideal: 720 }
                                    }
                                });
                                this.$refs.cameraPreview.srcObject = this.cameraStream;
                            } catch (e) {
                                console.error('Kamera Error:', e);
                                alert('Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan.');
                                this.closeCamera();
                            }
                        }
                    },

                    capturePhoto() {
                        const video = this.$refs.cameraPreview;
                        const canvas = this.$refs.cameraCanvas;
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0);
                        this.capturedPhoto = canvas.toDataURL('image/jpeg', 0.85);
                        this.stopCameraStream();
                    },

                    async usePhoto() {
                        this.isUploading = true;
                        setTimeout(async () => {
                            const canvas = this.$refs.cameraCanvas;
                            canvas.toBlob(async (blob) => {
                                const file = new File([blob], 'product-photo.jpg', { type: 'image/jpeg' });
                                try {
                                    await this.$wire.upload('photo', file);
                                    this.closeCamera();
                                } catch (err) {
                                    console.error('Upload Error:', err);
                                    alert('Gagal mengunggah foto. Silakan coba lagi.');
                                } finally {
                                    this.isUploading = false;
                                }
                            }, 'image/jpeg', 0.85);
                        }, 50);
                    },

                    retakePhoto() {
                        this.capturedPhoto = null;
                        this.openCamera();
                    },

                    closeCamera() {
                        this.stopCameraStream();
                        this.showCamera = false;
                        this.capturedPhoto = null;
                    },

                    stopCameraStream() {
                        if (this.cameraStream) {
                            this.cameraStream.getTracks().forEach(track => track.stop());
                            this.cameraStream = null;
                        }
                    }
                }" @close-modal.window="closeCamera()" class="bg-white border border-stone-200 rounded-[2.5rem] overflow-hidden shadow-sm">
                    <div class="p-8 border-b border-stone-100 flex items-center justify-between">
                        <h3 class="text-sm font-black text-stone-900 uppercase tracking-[0.2em]">Foto Produk</h3>
                        @if ($photo || $image_url)
                            <button type="button" wire:click="$set('photo', null); $set('image_url', null)" class="text-[10px] font-black text-red-500 hover:text-red-600 transition-all uppercase tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                Hapus
                            </button>
                        @endif
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="relative group aspect-square rounded-[2rem] bg-stone-50 border-2 border-dashed border-stone-200 flex items-center justify-center overflow-hidden transition-all hover:border-stone-900 shadow-inner">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($image_url)
                                <img src="{{ $image_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex flex-col items-center gap-4 text-stone-300 group-hover:text-stone-400 transition-colors">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Belum ada foto</span>
                                </div>
                            @endif

                            <div wire:loading wire:target="photo" class="absolute inset-0 bg-stone-900/10 backdrop-blur-[2px] flex items-center justify-center">
                                <svg class="animate-spin h-8 w-8 text-stone-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <label for="photo" class="flex flex-col items-center justify-center py-4 bg-stone-100 rounded-2xl cursor-pointer hover:bg-stone-200 transition-all group">
                                <svg class="w-5 h-5 text-stone-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-[10px] font-black text-stone-900 uppercase tracking-widest">Unggah</span>
                                <input wire:model="photo" id="photo" type="file" class="hidden" accept="image/*">
                            </label>

                            <button type="button" @click="openCamera()" class="flex flex-col items-center justify-center py-4 bg-stone-900 rounded-2xl cursor-pointer hover:bg-stone-800 transition-all group shadow-lg shadow-stone-200">
                                <svg class="w-5 h-5 text-white mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-[10px] font-black text-white uppercase tracking-widest">Kamera</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-stone-400 text-center font-bold uppercase tracking-widest">JPG, PNG, WebP. Maks 2MB.</p>

                        <!-- Alpine Camera Modal with x-cloak -->
                        <div x-show="showCamera" 
                             x-cloak
                             class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-8"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">
                            
                            <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-md" @click="closeCamera()"></div>
                            
                            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-2xl w-full overflow-hidden border border-white/20"
                                 x-transition:enter="transition ease-out duration-300 transform"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0 text-stone-900">
                                
                                <div class="px-10 py-8 border-b border-stone-100 flex items-center justify-between">
                                    <h4 class="text-xs font-black text-stone-900 uppercase tracking-[0.3em]">Bidik Foto Produk</h4>
                                    <button type="button" @click="closeCamera()" class="p-2 text-stone-400 hover:text-stone-900 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>

                                <div class="p-10">
                                    <div class="relative aspect-[4/3] rounded-[2rem] bg-stone-900 overflow-hidden shadow-2xl flex items-center justify-center">
                                        <video x-ref="cameraPreview" x-show="!capturedPhoto" autoplay playsinline class="w-full h-full object-cover"></video>
                                        <img :src="capturedPhoto" x-show="capturedPhoto && !isUploading" class="w-full h-full object-cover" x-cloak>
                                        <canvas x-ref="cameraCanvas" class="hidden"></canvas>

                                        <div x-show="isUploading" x-cloak class="absolute inset-0 bg-stone-900/60 backdrop-blur-md flex flex-col items-center justify-center text-white z-20">
                                            <svg class="animate-spin h-12 w-12 text-white mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-xs font-black uppercase tracking-[0.2em] animate-pulse">Mengunggah Ke Server...</span>
                                        </div>

                                        <div x-show="!capturedPhoto" x-cloak class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                            <div class="border-2 border-white/30 rounded-[2rem] w-[85%] h-[85%] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.3)]"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-10 py-10 bg-stone-50 border-t border-stone-100 flex justify-center gap-6">
                                    <button type="button" x-show="!capturedPhoto" x-cloak @click="capturePhoto()" class="px-12 py-4 bg-stone-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-stone-800 transition-all flex items-center gap-4 shadow-xl shadow-stone-200">
                                        <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                        Ambil Foto
                                    </button>

                                    <div x-show="capturedPhoto" x-cloak class="flex gap-4 w-full">
                                        <button type="button" @click="retakePhoto()" :disabled="isUploading" class="flex-1 px-8 py-4 border-2 border-stone-900 text-stone-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-white transition-all disabled:opacity-30">
                                            Ulangi
                                        </button>
                                        <button type="button" @click="usePhoto()" :disabled="isUploading" class="flex-1 px-8 py-4 bg-stone-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-stone-800 transition-all flex items-center justify-center gap-3 disabled:cursor-wait">
                                            <span x-show="!isUploading">Gunakan Foto</span>
                                            <span x-show="isUploading" class="flex items-center gap-2" x-cloak>
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Loading...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Inventory Sidebar Section -->
                <section class="bg-white/60 backdrop-blur-sm border border-stone-200 rounded-[2.5rem] p-8 shadow-sm space-y-8">
                    <div>
                        <x-input-label for="sku" :value="__('SKU (Kode Barang)')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                        <x-text-input wire:model="sku" id="sku" type="text" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all uppercase font-mono text-lg shadow-inner" placeholder="SKU-XXXXX" required />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="barcode" :value="__('Barcode / EAN')" class="text-stone-500 font-bold text-xs uppercase tracking-widest mb-3 ml-1" />
                        <div class="relative">
                            <x-text-input wire:model="barcode" id="barcode" type="text" class="block w-full px-6 py-4 bg-white border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-2xl transition-all shadow-inner" placeholder="Scan barcode di sini..." />
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 text-stone-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v0M8 8V4m0 4H4m4 0l4 4m8 8V4M16 12l-4 4m-4-4l4 4" /></svg>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                    </div>

                    <div class="pt-4">
                        <label for="is_active" class="flex items-center group cursor-pointer p-4 rounded-2xl bg-stone-50 border border-stone-100 hover:border-stone-200 transition-all">
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" id="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-stone-900"></div>
                            </div>
                            <span class="ms-4 text-sm font-bold text-stone-700 uppercase tracking-widest">Produk Aktif</span>
                        </label>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
