<div class="grid gap-3 px-5 py-5 sm:grid-cols-2 sm:px-7">
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Peran</p>
        <p class="mt-1 font-semibold text-ink">Siswa</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">NIS</p>
        <p class="mt-1 font-semibold text-ink">{{ $student?->nis ?: 'Belum diatur' }}</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Kelas</p>
        <p class="mt-1 font-semibold text-ink">{{ $enrollment?->class?->name ?: 'Belum terdaftar' }}</p>
    </div>
    <div class="rounded-xl border border-line bg-background px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">Tahun ajaran</p>
        <p class="mt-1 font-semibold text-ink">{{ $enrollment?->academic_year ?: 'Belum terdaftar' }}</p>
    </div>
</div>
