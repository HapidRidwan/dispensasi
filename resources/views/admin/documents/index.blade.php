<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Dokumen</h2>
                <p class="text-sm text-muted">Dokumen dispensasi yang telah diterbitkan.</p>
            </div>
            <a href="{{ route('admin.school-signatories.index') }}" class="text-sm text-primary hover:underline">Kelola Penandatangan</a>
        </div>

        <x-data-search placeholder="Cari nomor dokumen, pengajuan, atau status..." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Nomor</th>
                            <th class="px-5 py-3 font-medium">Pengajuan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Diterbitkan</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($documents as $doc)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $doc->document_number }}</td>
                                <td class="px-5 py-3 text-muted">{{ $doc->dispensation->title }}</td>
                                <td class="px-5 py-3"><x-ui.badge :status="$doc->document_status">{{ $doc->document_status }}</x-ui.badge></td>
                                <td class="px-5 py-3 text-muted">{{ $doc->generated_at?->format('d M Y H:i') }}</td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('admin.documents.show', $doc) }}" class="text-primary hover:underline">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada dokumen" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $documents->links() }}</div>
        </div>
    </div>
</x-app-layout>
