@props(['label', 'value', 'trend' => null])

<div class="group relative overflow-hidden rounded-2xl border border-line bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
    <span class="absolute inset-y-5 left-0 w-1 rounded-r-full bg-primary/70"></span>
    <div class="text-sm font-medium text-muted">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ $value }}</div>
    @if($trend)
        <div class="mt-2 text-xs text-muted">{{ $trend }}</div>
    @endif
</div>
