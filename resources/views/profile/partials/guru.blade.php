<div class="grid gap-3 px-5 py-5 sm:grid-cols-2 sm:px-7">
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Peran</p>
        <p class="mt-1 font-semibold text-ink">Guru</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">NIP</p>
        <p class="mt-1 font-semibold text-ink">{{ $teacher?->nip ?: 'Belum diatur' }}</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Jabatan</p>
        <p class="mt-1 font-semibold text-ink">{{ $teacher?->position ?: 'Belum diatur' }}</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Status akun</p>
        <p class="mt-1 font-semibold {{ auth()->user()->is_active && ($teacher?->is_active ?? true) ? 'text-green-700' : 'text-red-600' }}">
            {{ auth()->user()->is_active && ($teacher?->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
        </p>
    </div>
</div>
