<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">
            {{ __('Tambah Produk') }}
        </h2>
    </x-slot>

    <div class="bg-stone-50 min-h-screen">
        <livewire:products.product-form />
    </div>
</x-app-layout>
