<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-ink">Semua Pengajuan</h2>
            <p class="text-sm text-muted">Verifikasi pengajuan dispensasi siswa dan kegiatan.</p>
        </div>
        <x-ui.button onclick="window.location='{{ route('admin.dispensations.create') }}'">Buat Dispensasi Siswa</x-ui.button>

        <x-data-search placeholder="Cari pengaju, judul, jenis, atau status..." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Pengaju</th>
                            <th class="px-5 py-3 font-medium">Judul</th>
                            <th class="px-5 py-3 font-medium">Jenis</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($dispensations as $d)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $d->applicant->name }}</td>
                                <td class="px-5 py-3 text-ink">{{ $d->title }}</td>
                                <td class="px-5 py-3 text-muted">{{ $d->other_type ?: $d->type->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $d->date_start->format('d M Y') }}</td>
                                <td class="px-5 py-3"><x-ui.badge :status="$d->status">{{ $d->status }}</x-ui.badge></td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('admin.dispensations.show', $d) }}" class="text-primary hover:underline">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state title="Belum ada pengajuan" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $dispensations->links() }}</div>
        </div>
    </div>
</x-app-layout>
