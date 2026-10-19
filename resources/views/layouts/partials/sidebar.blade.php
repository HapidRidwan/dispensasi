@php($role = auth()->user()->role)

<aside
    id="app-sidebar"
    class="app-sidebar fixed inset-y-0 left-0 z-[60] {{ $role === 'siswa' ? 'hidden lg:flex' : 'flex' }} w-72 flex-col border-r border-line bg-white text-ink shadow-xl shadow-slate-900/5 transition-transform duration-300 ease-out lg:w-64 lg:translate-x-0 lg:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : 'max-lg:-translate-x-full'"
>
    <div class="flex flex-shrink-0 items-center justify-between gap-3 border-b border-line px-5 py-5">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
            <img src="{{ asset('images/icon/icon.png') }}" alt="" class="h-11 w-11 flex-shrink-0 rounded-xl object-cover ring-4 ring-blue-50">
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold leading-tight tracking-tight text-ink">{{ config('app.name', 'Dispensasi') }}</span>
                <span class="mt-1 block text-xs text-muted">Sistem Informasi Sekolah</span>
            </span>
        </a>
        <button
            type="button"
            @click="sidebarOpen = false"
            class="rounded-lg p-2 text-muted transition hover:bg-slate-100 hover:text-ink lg:hidden"
            aria-label="Tutup navigasi"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="m6 6 12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5 text-sm" aria-label="Navigasi utama">
        @if($role === 'admin')
            @include('layouts.partials.menu-admin')
        @elseif($role === 'guru')
            @include('layouts.partials.menu-guru')
        @else
            @include('layouts.partials.menu-siswa')
        @endif
    </nav>

    <div class="flex-shrink-0 border-t border-line bg-slate-50/70 px-4 py-4">
        <a href="{{ route($role.'.profile') }}"
           class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs($role.'.profile') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
            <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profil
        </a>
    </div>
</aside>
