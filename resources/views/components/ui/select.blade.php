@props(['label' => null, 'name', 'options' => [], 'error' => null])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    @endif
    <select {!! $attributes->merge([
        'id' => $name,
        'name' => $name,
        'class' => 'mt-1 block w-full rounded-lg border-line text-sm focus:border-primary focus:ring-primary/30',
    ]) !!}>{{ $slot }}</select>
    @if($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
