@props(['variant' => 'info'])

@php
    $classes = match ($variant) {
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'danger' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        default => 'border-blue-200 bg-blue-50 text-blue-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm $classes"]) }}>
    {{ $slot }}
</div>
