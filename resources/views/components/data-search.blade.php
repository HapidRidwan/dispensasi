@props(['placeholder' => 'Cari data...', 'label' => 'Cari'])

<form method="GET" {{ $attributes->merge(['class' => 'flex w-full flex-col gap-2 sm:w-auto sm:flex-row']) }}>
    @foreach(request()->query() as $key => $value)
        @if($key !== 'q' && $key !== 'page' && is_scalar($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <label class="relative block w-full sm:w-80">
        <span class="sr-only">{{ $label }}</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
        </svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" class="w-full rounded-xl border-line bg-white py-2.5 pl-9 pr-3 text-sm text-ink shadow-sm placeholder:text-slate-400 focus:border-primary focus:ring-primary/20">
    </label>
    <x-ui.button type="submit" variant="secondary" class="shrink-0">Cari</x-ui.button>
</form>
