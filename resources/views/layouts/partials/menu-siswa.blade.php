{{-- Dashboard --}}
<a href="{{ route('siswa.dashboard') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('siswa.dashboard') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    Dashboard
</a>

{{-- Dispensasi --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Dispensasi Saya</div>
<a href="{{ route('siswa.dispensations.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('siswa.dispensations.*') && !request()->routeIs('siswa.dispensations.create') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Riwayat Pengajuan
</a>
<a href="{{ route('siswa.dispensations.create') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('siswa.dispensations.create') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M12 4v16m8-8H4"/>
    </svg>
    Ajukan Dispensasi
</a>

{{-- Notifikasi --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Lainnya</div>
<a href="{{ route('siswa.notifications.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('siswa.notifications.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>
    Notifikasi
</a>
