<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dispensasi') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/icon/icon.png') }}">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#163A5F">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-background font-sans antialiased text-ink">
        <div class="app-page-enter min-h-screen lg:grid lg:grid-cols-2">
            <section class="relative hidden min-h-screen flex-col justify-between overflow-hidden bg-gradient-to-br from-primary via-[#194c77] to-primary-dark p-10 text-white lg:flex xl:p-14">
                <div class="pointer-events-none absolute -right-24 -top-20 h-80 w-80 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute right-20 top-1/3 h-40 w-40 rounded-full bg-white/5 blur-2xl"></div>

                <a href="{{ route('login') }}" class="relative z-10 flex items-center gap-3">
                    <img src="{{ asset('images/icon/icon.png') }}" alt="" class="h-12 w-12 rounded-2xl object-cover ring-1 ring-white/20">
                    <span>
                        <span class="block text-base font-semibold tracking-tight">{{ config('app.name', 'Dispensasi') }}</span>
                        <span class="mt-0.5 block text-xs text-white/65">Sistem Informasi Sekolah</span>
                    </span>
                </a>

                <div class="relative z-10 max-w-xl py-16">
                    <h1 class="text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">
                        Semua urusan dispensasi, lebih mudah.
                    </h1>
                    <p class="mt-5 max-w-md text-base leading-7 text-blue-100/80">
                        Ajukan dan pantau dispensasi sekolah melalui satu tempat yang praktis, aman, dan mudah digunakan.
                    </p>
                </div>

                <p class="relative z-10 text-xs text-white/50">© {{ date('Y') }} {{ config('app.name', 'Dispensasi') }}</p>
            </section>

            <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-8 lg:bg-white lg:px-12 xl:px-20">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <img src="{{ asset('images/icon/icon.png') }}" alt="" class="h-12 w-12 rounded-2xl object-cover shadow-sm">
                        <span>
                            <span class="block text-sm font-semibold text-ink">{{ config('app.name', 'Dispensasi') }}</span>
                            <span class="mt-0.5 block text-xs text-muted">Sistem Informasi Sekolah</span>
                        </span>
                    </div>

                    @if (request()->routeIs('login'))
                        <div class="mb-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Selamat datang</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Masuk ke akun Anda</h2>
                            <p class="mt-2 text-sm leading-6 text-muted">Gunakan username dan password untuk melanjutkan.</p>
                        </div>
                    @elseif (request()->routeIs('password.request'))
                        <div class="mb-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Pemulihan akun</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Lupa password?</h2>
                            <p class="mt-2 text-sm leading-6 text-muted">Ikuti langkah berikut untuk mendapatkan akses kembali.</p>
                        </div>
                    @elseif (request()->routeIs('password.reset'))
                        <div class="mb-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Pemulihan akun</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Buat password baru</h2>
                            <p class="mt-2 text-sm leading-6 text-muted">Gunakan password baru untuk mengamankan akun Anda.</p>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-xl shadow-slate-900/5 sm:p-8 lg:rounded-none lg:border-0 lg:p-0 lg:shadow-none">
                        {{ $slot }}
                    </div>

                    <p class="mt-8 text-center text-xs text-muted lg:text-left">
                        © {{ date('Y') }} {{ config('app.name', 'Dispensasi') }}
                    </p>
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
