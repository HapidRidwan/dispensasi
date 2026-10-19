@php($role = auth()->user()->role)

@if($role === 'siswa')
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-line bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_24px_rgba(15,39,64,0.06)] backdrop-blur lg:hidden" aria-label="Navigasi siswa">
        <div class="mx-auto grid h-[4.5rem] max-w-xl grid-cols-4 px-1">
            <a href="{{ route('siswa.dashboard') }}"
               @class(['flex flex-col items-center justify-center gap-1 text-[10px] font-medium transition', 'text-primary' => request()->routeIs('siswa.dashboard'), 'text-muted hover:text-ink' => !request()->routeIs('siswa.dashboard')])
               aria-current="{{ request()->routeIs('siswa.dashboard') ? 'page' : 'false' }}">
                <span @class(['flex h-8 w-10 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs('siswa.dashboard')])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/>
                    </svg>
                </span>
                Beranda
            </a>
            <a href="{{ route('siswa.dispensations.index') }}"
               @class(['flex flex-col items-center justify-center gap-1 text-[9px] font-medium transition', 'text-primary' => request()->routeIs('siswa.dispensations.*') && !request()->routeIs('siswa.dispensations.create'), 'text-muted hover:text-ink' => !request()->routeIs('siswa.dispensations.*') || request()->routeIs('siswa.dispensations.create')])
               aria-current="{{ request()->routeIs('siswa.dispensations.*') && !request()->routeIs('siswa.dispensations.create') ? 'page' : 'false' }}">
                <span @class(['flex h-8 w-10 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs('siswa.dispensations.*') && !request()->routeIs('siswa.dispensations.create')])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6"/>
                    </svg>
                </span>
                Riwayat
            </a>
            <a href="{{ route('siswa.dispensations.create') }}"
               @class(['flex flex-col items-center justify-center gap-1 text-[9px] font-medium transition', 'text-primary' => request()->routeIs('siswa.dispensations.create'), 'text-muted hover:text-ink' => !request()->routeIs('siswa.dispensations.create')])
               aria-current="{{ request()->routeIs('siswa.dispensations.create') ? 'page' : 'false' }}">
                <span @class(['flex h-8 w-10 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs('siswa.dispensations.create')])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M12 5v14m7-7H5"/>
                    </svg>
                </span>
                Ajukan
            </a>
            <a href="{{ route('siswa.profile') }}"
               @class(['flex flex-col items-center justify-center gap-1 text-[10px] font-medium transition', 'text-primary' => request()->routeIs('siswa.profile'), 'text-muted hover:text-ink' => !request()->routeIs('siswa.profile')])
               aria-current="{{ request()->routeIs('siswa.profile') ? 'page' : 'false' }}">
                <span @class(['flex h-8 w-10 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs('siswa.profile')])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/>
                    </svg>
                </span>
                Profil
            </a>
        </div>
    </nav>
@else
<nav class="fixed inset-x-0 bottom-0 z-50 border-t border-line bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_24px_rgba(15,39,64,0.06)] backdrop-blur lg:hidden" aria-label="Navigasi utama">
    <div class="mx-auto grid h-[4.5rem] max-w-xl grid-cols-4 px-2">
        <a href="{{ route($role === 'guru' ? 'guru.dashboard' : 'siswa.dashboard') }}"
           @class(['flex flex-col items-center justify-center gap-1 text-[10px] font-medium transition', 'text-primary' => request()->routeIs($role.'.dashboard'), 'text-muted hover:text-ink' => !request()->routeIs($role.'.dashboard')])
           aria-current="{{ request()->routeIs($role.'.dashboard') ? 'page' : 'false' }}">
            <span @class(['flex h-8 w-11 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs($role.'.dashboard')])>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/>
                </svg>
            </span>
            Beranda
        </a>
        <a href="{{ route($role === 'guru' ? 'guru.dispensations.index' : 'siswa.dispensations.index') }}"
           @class(['flex flex-col items-center justify-center gap-1 text-[10px] font-medium transition', 'text-primary' => request()->routeIs($role.'.dispensations.*') && !request()->routeIs($role.'.dispensations.create'), 'text-muted hover:text-ink' => !request()->routeIs($role.'.dispensations.*') || request()->routeIs($role.'.dispensations.create')])
           aria-current="{{ request()->routeIs($role.'.dispensations.*') && !request()->routeIs($role.'.dispensations.create') ? 'page' : 'false' }}">
            <span @class(['flex h-8 w-11 items-center justify-center rounded-xl transition', 'bg-blue-50' => request()->routeIs($role.'.dispensations.*') && !request()->routeIs($role.'.dispensations.create')])>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6"/>
                </svg>
            </span>
            Dispensasi
        </a>
        <a href="{{ route($role === 'guru' ? 'guru.dispensations.create' : 'siswa.dispensations.create') }}"
           class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-primary transition"
           aria-current="{{ request()->routeIs($role.'.dispensations.create') ? 'page' : 'false' }}">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-white shadow-sm shadow-primary/20 transition hover:bg-primary-dark">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5"/>
                </svg>
            </span>
            Ajukan
        </a>
        <button type="button" @click="sidebarOpen = !sidebarOpen"
                aria-controls="app-sidebar"
                :aria-expanded="sidebarOpen.toString()"
                class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-muted transition hover:text-ink"
                :class="sidebarOpen ? 'text-primary' : 'text-muted'">
            <span class="flex h-8 w-11 items-center justify-center rounded-xl transition"
                  :class="sidebarOpen ? 'bg-blue-50' : ''">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </span>
            Lainnya
        </button>
    </div>
</nav>
@endif
