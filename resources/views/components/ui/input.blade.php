@props(['label' => null, 'name' => null, 'error' => null, 'help' => null, 'toggleable' => false])

<div @if($toggleable) x-data="{ passwordVisible: false }" @endif>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    @endif
    @if($toggleable)
        <div class="relative mt-1">
            <input {!! $attributes->merge([
                'id' => $name,
                'name' => $name,
                'class' => 'block w-full rounded-lg border-line pr-12 text-sm focus:border-primary focus:ring-primary/30 '.($error ? 'border-red-300 text-red-900' : ''),
            ], $escape = false) !!} x-bind:type="passwordVisible ? 'text' : 'password'">
            <button
                type="button"
                class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-muted transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/40"
                x-on:click="passwordVisible = !passwordVisible"
                x-bind:aria-pressed="passwordVisible.toString()"
                x-bind:aria-label="passwordVisible ? 'Sembunyikan password' : 'Tampilkan password'"
            >
                <svg x-cloak x-show="!passwordVisible" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg x-cloak x-show="passwordVisible" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.2 0 9.8 7 9.8 7a17 17 0 0 1-3.1 3.8M6.2 6.2C3.7 7.8 2.2 12 2.2 12s3.6 7 9.8 7c1.3 0 2.5-.3 3.5-.8"/>
                </svg>
            </button>
        </div>
    @else
        <input {!! $attributes->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'mt-1 block w-full rounded-lg border-line text-sm focus:border-primary focus:ring-primary/30 '.($error ? 'border-red-300 text-red-900' : ''),
        ], $escape = false) !!}>
    @endif
    @if($help)
        <p class="mt-1 text-xs text-muted">{{ $help }}</p>
    @endif
    @if($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
