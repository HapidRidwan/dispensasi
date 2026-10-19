{{-- Dashboard --}}
<a href="{{ route('admin.dashboard') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.dashboard') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    Dashboard
</a>

{{-- Dispensasi --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Dispensasi</div>
<a href="{{ route('admin.dispensations.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.dispensations.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Semua Pengajuan
</a>
<a href="{{ route('admin.dispensations.create') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.dispensations.create') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M12 4v16m8-8H4"/>
    </svg>
    Buat Dispensasi Siswa
</a>
<a href="{{ route('admin.activities.create') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.activities.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    Buat Kegiatan Formal
</a>
<a href="{{ route('admin.dispensation-types.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.dispensation-types.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    Jenis Dispensasi
</a>

{{-- Data Sekolah --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Data Sekolah</div>
<a href="{{ route('admin.students.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.students.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    Siswa
</a>
<a href="{{ route('admin.teachers.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.teachers.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    Guru
</a>
<a href="{{ route('admin.classes.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.classes.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    Kelas
</a>
<a href="{{ route('admin.teacher-assignments.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.teacher-assignments.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    Penugasan Guru
</a>
<a href="{{ route('admin.users.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.users.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    Manajemen User
</a>

{{-- Dokumen & Laporan --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Dokumen & Laporan</div>
<a href="{{ route('admin.documents.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.documents.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
    </svg>
    Dokumen
</a>
<a href="{{ route('admin.reports.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.reports.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    Laporan
</a>

{{-- Lainnya --}}
<div class="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Lainnya</div>
<a href="{{ route('admin.notifications.index') }}"
   class="flex items-center gap-3 rounded-xl border-l-2 px-3 py-2.5 text-[13px] font-medium transition {{ request()->routeIs('admin.notifications.*') ? 'border-primary bg-blue-50 text-primary' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-ink' }}">
    <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>
    Notifikasi
</a>
