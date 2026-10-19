<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Dispensasi') }} — {{ config('app.name', 'Dispensasi') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon/icon.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#163A5F">

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-ink">
    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
        @if(auth()->user()->role !== 'siswa')
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-50 bg-slate-950/30 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
                x-cloak
            ></div>
        @endif

        @include('layouts.partials.sidebar')

        <div class="flex min-h-screen min-w-0 flex-col lg:ml-64">
            <header class="sticky top-0 z-20 border-b border-line bg-white/90 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        @if(auth()->user()->role !== 'siswa')
                            <button
                                @click="sidebarOpen = true"
                                class="rounded-xl p-2 text-muted transition hover:bg-blue-50 hover:text-primary lg:hidden"
                                aria-label="Buka navigasi"
                                aria-controls="app-sidebar"
                                :aria-expanded="sidebarOpen.toString()"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                        @endif
                        <span class="truncate text-sm font-semibold tracking-tight text-ink sm:text-base">
                            {{ $pageTitle ?? ($title ?? 'Dashboard') }}
                        </span>
                    </div>

                    <div class="flex flex-shrink-0 items-center gap-2 sm:gap-4">
                        @include('components.navigation.notification-dropdown')

                        <div class="hidden items-center gap-2.5 sm:flex">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-primary">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="max-w-40">
                                <div class="truncate text-sm font-medium leading-tight text-ink">{{ auth()->user()->name }}</div>
                                <div class="mt-0.5 text-xs capitalize text-muted">{{ auth()->user()->role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @include('layouts.partials.flash-message')

            <main class="app-page-enter flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8 {{ in_array(auth()->user()->role, ['guru', 'siswa'], true) ? 'pb-28 lg:pb-8' : '' }}">
                {{ $slot }}
            </main>
        </div>

        @if(in_array(auth()->user()->role, ['guru', 'siswa'], true))
            @include('layouts.partials.mobile-bottom-nav')
        @endif
    </div>

    <div id="toast"
         class="fixed bottom-24 right-4 z-50 hidden max-w-sm rounded-xl bg-primary px-4 py-3 text-sm text-white shadow-lg transition-all lg:bottom-4">
    </div>

    @livewireScripts

    <script>
        (function () {
            if (!window.Echo || !document.getElementById('notif-count')) {
                return;
            }

            if (window._notifListenerRegistered) {
                return;
            }
            window._notifListenerRegistered = true;

            window.Echo.private('user.{{ auth()->id() }}')
                .listen('.notification.created', (e) => {
                    const count = document.getElementById('notif-count');
                    if (count) {
                        const next = (parseInt(count.textContent || '0') + 1);
                        count.textContent = next.toString();
                        count.classList.remove('hidden');
                    }

                    const list = document.getElementById('notif-list');
                    if (list) {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'block px-4 py-3 hover:bg-background border-b border-line text-sm';
                        item.innerHTML = '<div class="font-medium text-ink">' + (e.title || '') + '</div>'
                            + '<div class="text-xs text-muted mt-0.5">' + (e.message || '') + '</div>';
                        list.prepend(item);
                    }

                    const toast = document.getElementById('toast');
                    if (toast) {
                        toast.textContent = (e.title ? e.title + ': ' : '') + (e.message || '');
                        toast.classList.remove('hidden');
                        clearTimeout(window._toastTimer);
                        window._toastTimer = setTimeout(() => toast.classList.add('hidden'), 5000);
                    }
                });

            @if(auth()->user()->role === 'admin')
            window.Echo.private('admin')
                .listen('.notification.created', (e) => {
                    const toast = document.getElementById('toast');
                    if (toast) {
                        toast.textContent = '[Admin] ' + (e.title ? e.title + ': ' : '') + (e.message || '');
                        toast.classList.remove('hidden');
                        clearTimeout(window._toastTimerAdmin);
                        window._toastTimerAdmin = setTimeout(() => toast.classList.add('hidden'), 5000);
                    }
                });
            @endif
        })();
    </script>
    @stack('scripts')
</body>
</html>
