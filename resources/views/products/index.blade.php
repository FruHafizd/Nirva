<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <livewire:products.product-list />
</x-app-layout>
