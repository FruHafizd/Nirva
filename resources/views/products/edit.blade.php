<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">
            {{ __('Edit Produk') }}
        </h2>
    </x-slot>

    <div class="bg-stone-50 min-h-screen">
        <livewire:products.product-form :product="$product" />
    </div>
</x-app-layout>
