<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }" class="relative">
    <!-- Mobile Navigation Bar -->
    <div class="lg:hidden flex items-center justify-between px-4 py-4 bg-white border-b border-stone-200 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate>
                <x-application-logo class="w-8 h-8 fill-current text-stone-900" />
            </a>
            <span class="text-lg font-semibold tracking-tight text-stone-900">{{ config('app.name') }}</span>
        </div>
        <button @click="open = !open" class="p-2 rounded-lg text-stone-500 hover:bg-stone-100 transition-colors">
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Sidebar Wrapper -->
    <div 
        x-cloak
        :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 w-64 bg-white border-r border-stone-200 z-30 transform transition-transform duration-300 ease-in-out lg:static lg:h-screen lg:flex lg:flex-col lg:z-0"
    >
        <!-- Top: Logo & Branding -->
        <div class="px-6 py-8 flex items-center gap-3 border-b border-stone-50 lg:border-none">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <x-application-logo class="w-9 h-9 fill-current text-stone-900" />
                <span class="text-xl font-bold tracking-tight text-stone-900 leading-none">{{ config('app.name') }}</span>
            </a>
        </div>

        <!-- Middle: Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <x-nav-link 
                :href="route('dashboard')" 
                :active="request()->routeIs('dashboard')" 
                wire:navigate 
            >
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('Dasbor') }}</span>
            </x-nav-link>

            <x-nav-link 
                :href="route('products.index')" 
                :active="request()->routeIs('products.*')" 
                wire:navigate 
            >
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>{{ __('Produk') }}</span>
            </x-nav-link>

            <div class="pt-4 pb-2 px-3">
                <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest">{{ __('Penjualan') }}</span>
            </div>

            <x-nav-link 
                :href="route('transactions.pos')" 
                :active="request()->routeIs('transactions.pos')" 
                wire:navigate 
            >
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>{{ __('Kasir (POS)') }}</span>
            </x-nav-link>

            <x-nav-link 
                :href="route('transactions.index')" 
                :active="request()->routeIs('transactions.index')" 
                wire:navigate 
            >
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>{{ __('Riwayat Transaksi') }}</span>
            </x-nav-link>

            <div class="pt-4 pb-2 px-3">
                <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest">{{ __('Sistem') }}</span>
            </div>

            <x-nav-link 
                :href="route('profile')" 
                :active="request()->routeIs('profile')" 
                wire:navigate 
            >
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ __('Pengaturan Profil') }}</span>
            </x-nav-link>
        </nav>

        <!-- Bottom: User & Logout -->
        <div class="px-4 py-4 border-t border-stone-100">
            <div class="flex items-center gap-3 px-3 py-3 mb-2">
                <div class="w-9 h-9 rounded-full bg-stone-100 flex items-center justify-center text-stone-600 font-semibold text-xs border border-stone-200">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-semibold text-stone-900 truncate">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-stone-500 truncate">{{ auth()->user()->email }}</span>
                </div>
            </div>

            <button 
                wire:click="logout" 
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-stone-600 hover:text-red-600 hover:bg-red-50 transition-all group cursor-pointer"
            >
                <svg class="w-5 h-5 transition-colors group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>{{ __('Keluar') }}</span>
            </button>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div 
        x-show="open" 
        @click="open = false" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-stone-900/20 backdrop-blur-sm z-20 lg:hidden"
    ></div>
</div>
