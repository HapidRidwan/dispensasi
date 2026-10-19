@props(['show' => false])

<div x-data="{}" x-cloak {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4']) }} @if(! $attributes->has('static')) @click.self="$el.classList.add('hidden')" @endif>
    <div class="w-full max-w-lg bg-surface rounded-xl border border-line p-6 shadow-lg">
        @if(isset($title))
            <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
        @endif
        <div class="mt-3 text-sm text-muted">{{ $slot }}</div>
        @isset($footer)
            <div class="mt-5 flex justify-end gap-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
