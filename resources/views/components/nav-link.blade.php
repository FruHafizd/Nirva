@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 bg-stone-100 text-stone-900 shadow-sm ring-1 ring-stone-950/5'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-stone-500 hover:text-stone-900 hover:bg-stone-50 group hover:translate-x-0.5';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
