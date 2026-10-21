<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Kegiatan Formal</h2>
                <p class="text-sm text-muted">Pengajuan kegiatan dengan peserta lintas kelas.</p>
            </div>
            <x-ui.button onclick="window.location='{{ route('guru.activities.create') }}'">Buat Kegiatan</x-ui.button>
        </div>

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Kegiatan</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Peserta</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($activities as $a)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $a->title }}</td>
                                <td class="px-5 py-3 text-muted">{{ $a->date_start->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-muted">{{ $a->recipients_count }} siswa</td>
                                <td class="px-5 py-3"><x-ui.badge :status="$a->status">{{ $a->status }}</x-ui.badge></td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('guru.activities.show', $a) }}" class="text-primary hover:underline">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada kegiatan" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $activities->links() }}</div>
        </div>
    </div>
</x-app-layout>
