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

<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-stone-200 rounded-3xl shadow-xl shadow-stone-200/50 overflow-hidden">
        <!-- Header -->
        <div class="px-8 py-6 bg-stone-900 border-b border-stone-800">
            <h3 class="text-xl font-bold text-white tracking-tight">
                {{ $product ? 'Edit Detail Produk' : 'Tambah Produk Baru' }}
            </h3>
            <p class="text-stone-400 text-sm mt-1">
                {{ $product ? "Perbarui informasi untuk {$product->sku}" : "Isi detail untuk menambahkan item baru ke inventaris Anda." }}
            </p>
        </div>

        <form wire:submit="save" class="p-8 space-y-8">
            <!-- Product Photo Section -->
            <div x-data="{ 
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
                    
                    // Gunakan setTimeout agar UI sempat me-render indicator Memproses 
                    // sebelum browser melakukan pemrosesan data biner & upload.
                    setTimeout(async () => {
                        const canvas = this.$refs.cameraCanvas;
                        canvas.toBlob(async (blob) => {
                            const file = new File([blob], 'product-photo.jpg', { type: 'image/jpeg' });
                            try {
                                // Pastikan menggunakan magic property $wire atau this.$wire
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
            }" @close-modal.window="closeCamera()">
                <x-input-label for="photo" :value="__('Foto Produk')" class="text-stone-600 font-semibold mb-4" />
                <div class="flex items-start gap-6">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-2xl bg-stone-100 border-2 border-dashed border-stone-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-stone-900">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($image_url)
                                <img src="{{ $image_url }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-stone-900/10 backdrop-blur-[2px] rounded-2xl flex items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-stone-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2">
                            <label for="photo" class="px-4 py-2 bg-stone-100 text-stone-900 text-sm font-bold rounded-xl cursor-pointer hover:bg-stone-200 transition-colors">
                                Pilih Gambar
                                <input wire:model="photo" id="photo" type="file" class="hidden" accept="image/*">
                            </label>
                            
                            <button type="button" 
                                    @click="openCamera()"
                                    class="px-4 py-2 bg-stone-900 text-white text-sm font-bold rounded-xl cursor-pointer hover:bg-stone-800 transition-colors flex items-center gap-2 shadow-lg shadow-stone-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Foto Langsung
                            </button>

                            @if ($photo || $image_url)
                                <button type="button" wire:click="$set('photo', null); $set('image_url', null)" class="ml-2 text-xs font-bold text-red-500 hover:text-red-600 transition-colors">
                                    Hapus
                                </button>
                            @endif
                        </div>
                        <p class="text-xs text-stone-400">JPG, PNG, WebP. Maksimal 2MB.</p>
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                    </div>
                </div>

                <!-- Camera Modal -->
                <div x-show="showCamera" 
                     class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="closeCamera()"></div>
                    
                    <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-stone-100"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        
                        <!-- Modal Header -->
                        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between bg-stone-50">
                            <h4 class="text-sm font-bold text-stone-900 uppercase tracking-widest">Ambil Foto Produk</h4>
                            <button type="button" @click="closeCamera()" class="p-2 text-stone-400 hover:text-stone-900 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="p-6">
                            <div class="relative aspect-video rounded-2xl bg-stone-900 overflow-hidden shadow-inner flex items-center justify-center">
                                <!-- Camera Preview -->
                                <video x-ref="cameraPreview" 
                                       x-show="!capturedPhoto"
                                       autoplay 
                                       playsinline 
                                       class="w-full h-full object-cover"></video>
                                
                                <!-- Captured Photo Preview -->
                                <img :src="capturedPhoto" 
                                     x-show="capturedPhoto && !isUploading" 
                                     class="w-full h-full object-cover">
                                     
                                <!-- Hidden Canvas -->
                                <canvas x-ref="cameraCanvas" class="hidden"></canvas>

                                <!-- Processing/Uploading Overlay -->
                                <div x-show="isUploading" 
                                     class="absolute inset-0 bg-stone-900/40 backdrop-blur-sm flex flex-col items-center justify-center text-white z-10"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100">
                                    <svg class="animate-spin h-10 w-10 text-white mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-widest animate-pulse">Memproses Foto...</span>
                                </div>

                                <!-- Guides -->
                                <div x-show="!capturedPhoto" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                    <div class="border-2 border-white/20 rounded-xl w-4/5 h-4/5 border-dashed"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-6 bg-stone-50 border-t border-stone-100 flex justify-center gap-4">
                            <!-- Capture Button -->
                            <button type="button" 
                                    x-show="!capturedPhoto"
                                    @click="capturePhoto()"
                                    class="px-8 py-3 bg-stone-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-800 transition-all flex items-center gap-3">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.8)]"></div>
                                Ambil Foto
                            </button>

                            <!-- Use/Retake Buttons -->
                            <div x-show="capturedPhoto" class="flex gap-3 w-full">
                                <button type="button" 
                                        @click="retakePhoto()"
                                        :disabled="isUploading"
                                        class="flex-1 px-6 py-3 border-2 border-stone-900 text-stone-900 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-100 transition-all disabled:opacity-30 disabled:border-stone-200">
                                    Ulangi
                                </button>
                                <button type="button" 
                                        @click="usePhoto()"
                                        :disabled="isUploading"
                                        class="flex-1 px-6 py-3 bg-stone-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-stone-800 transition-all flex items-center justify-center gap-3 disabled:bg-stone-700 disabled:cursor-wait min-w-[140px]">
                                    <span x-show="!isUploading">Gunakan Foto</span>
                                    <span x-show="isUploading" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-stone-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Information Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-input-label for="name" :value="__('Nama Produk')" class="text-stone-600 font-semibold mb-2" />
                    <x-text-input wire:model="name" id="name" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="sku" :value="__('SKU (Kode Stok Barang)')" class="text-stone-600 font-semibold mb-2" />
                    <x-text-input wire:model="sku" id="sku" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all uppercase" required />
                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="category_id" :value="__('Kategori')" class="text-stone-600 font-semibold mb-2" />
                    <select wire:model="category_id" id="category_id" class="block w-full py-2 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all text-stone-900">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>
            </div>

            <div class="border-t border-stone-100 pt-8">
                <x-input-label for="description" :value="__('Deskripsi (Opsional)')" class="text-stone-600 font-semibold mb-2" />
                <textarea wire:model="description" id="description" rows="3" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all text-stone-900"></textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- Pricing & Inventory Section -->
            <div class="border-t border-stone-100 pt-8">
                <h4 class="text-sm font-bold text-stone-400 uppercase tracking-widest mb-6">Harga & Inventaris</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="price" :value="__('Harga Jual (Rp)')" class="text-stone-600 font-semibold mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 font-mono text-sm">Rp</div>
                            <x-text-input wire:model="price" id="price" type="number" step="0.01" class="block w-full pl-10 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        </div>
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="cost_price" :value="__('Harga Modal (Rp)')" class="text-stone-600 font-semibold mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 font-mono text-sm">Rp</div>
                            <x-text-input wire:model="cost_price" id="cost_price" type="number" step="0.01" class="block w-full pl-10 bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" />
                        </div>
                        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="stock" :value="__('Stok Saat Ini')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="stock" id="stock" type="number" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit" :value="__('Satuan (pcs, kg, dll)')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="unit" id="unit" type="text" class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all" required />
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Details Section -->
            <div class="border-t border-stone-100 pt-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="barcode" :value="__('Barcode (Opsional)')" class="text-stone-600 font-semibold mb-2" />
                        <x-text-input wire:model="barcode" 
                                      id="barcode" 
                                      type="text" 
                                      class="block w-full bg-stone-50 border-stone-200 focus:border-stone-900 focus:ring-stone-900 rounded-xl transition-all {{ request()->query('barcode') ? 'ring-2 ring-stone-900/10 border-stone-900' : '' }}" />
                        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                    </div>

                    <div class="flex items-center pt-8">
                        <label for="is_active" class="inline-flex items-center cursor-pointer">
                            <input wire:model="is_active" id="is_active" type="checkbox" class="rounded-lg bg-stone-100 border-stone-200 text-stone-900 shadow-sm focus:ring-stone-900 transition-all h-5 w-5">
                            <span class="ms-3 text-sm font-bold text-stone-700">Atur sebagai Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-8 border-t border-stone-100 flex items-center justify-between">
                <a href="{{ route('products.index') }}" class="text-sm font-bold text-stone-400 hover:text-stone-900 transition-colors uppercase tracking-widest">
                    Batal
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-stone-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-stone-800 focus:bg-stone-800 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-stone-200">
                    <span wire:loading.remove>Simpan Produk</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>
