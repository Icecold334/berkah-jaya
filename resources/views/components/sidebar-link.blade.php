@props(['href', 'icon' => '', 'active' => false])

@php
    $classes = $active
        ? 'flex items-center p-2 text-primary-700 bg-gray-100 rounded-lg transition duration-200'
        : 'flex items-center p-2 text-white rounded-lg hover:text-primary-700 hover:bg-gray-100 transition duration-200';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <i class="{{ $icon }}"></i>
    <span class="ml-3">{{ $slot }}</span>
</a>
