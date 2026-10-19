<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6 lg:space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary">Ruang siswa</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Dispensasi saya</h1>
                <p class="mt-1.5 text-sm text-muted">Pantau status dan riwayat pengajuan dispensasi Anda.</p>
            </div>
            <x-ui.button class="hidden sm:inline-flex" onclick="window.location='{{ route('siswa.dispensations.create') }}'">Ajukan dispensasi</x-ui.button>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-cards.stat-card label="Pengajuan Aktif" :value="$active" />
            <x-cards.stat-card label="Disetujui" :value="$approved" />
            <x-cards.stat-card label="Ditolak" :value="$rejected" />
            <x-cards.stat-card label="Total Pengajuan" :value="$total" />
        </div>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-ink">Riwayat terbaru</h2>
                    <p class="mt-1 text-xs text-muted">Status terkini dari pengajuan Anda.</p>
                </div>
                <a href="{{ route('siswa.dispensations.index') }}" class="flex-shrink-0 text-sm font-medium text-primary transition hover:text-primary-dark">Lihat semua <span aria-hidden="true">→</span></a>
            </div>
            <div class="divide-y divide-line/80 md:hidden">
                @forelse($latest as $d)
                    <a href="{{ route('siswa.dispensations.show', $d) }}" class="block px-5 py-4 transition hover:bg-slate-50/70">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">{{ $d->title }}</p>
                                <p class="mt-1 text-xs text-muted">{{ $d->other_type ?: $d->type->name }} <span class="px-1">·</span> {{ $d->date_start->format('d M Y') }}</p>
                            </div>
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </div>
                        <div class="mt-3"><x-ui.badge :status="$d->status">{{ $d->status }}</x-ui.badge></div>
                    </a>
                @empty
                    <x-ui.empty-state title="Belum ada pengajuan" description="Ajukan dispensasi pertama Anda." />
                @endforelse
            </div>
            <div class="overflow-x-auto">
                <table class="hidden w-full min-w-[640px] text-sm md:table">
                    <thead>
                        <tr class="border-b border-line bg-slate-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.1em] text-muted">
                            <th class="px-5 py-3.5 font-medium sm:px-6">Judul</th>
                            <th class="px-5 py-3.5 font-medium">Jenis</th>
                            <th class="px-5 py-3.5 font-medium">Tanggal</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                            <th class="px-5 py-3.5 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/80">
                        @forelse($latest as $d)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-medium text-ink sm:px-6">{{ $d->title }}</td>
                                <td class="px-5 py-4 text-muted">{{ $d->other_type ?: $d->type->name }}</td>
                                <td class="px-5 py-4 text-muted">{{ $d->date_start->format('d M Y') }}</td>
                                <td class="px-5 py-4"><x-ui.badge :status="$d->status">{{ $d->status }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('siswa.dispensations.show', $d) }}" class="font-medium text-primary hover:text-primary-dark">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada pengajuan" description="Ajukan dispensasi pertama Anda." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
