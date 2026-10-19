@props(['type' => 'button', 'variant' => 'primary', 'disabled' => false])

@php
    $classes = match ($variant) {
        'secondary' => 'bg-surface border border-line text-ink hover:bg-background',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'ghost' => 'text-ink hover:bg-background',
        default => 'bg-primary text-white hover:bg-primary-dark',
    };
@endphp

<button {!! $attributes->merge([
    'type' => $type,
    'class' => "inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed $classes",
    'disabled' => $disabled,
]) !!}>{{ $slot }}</button>
