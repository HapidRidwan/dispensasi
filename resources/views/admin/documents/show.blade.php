<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">{{ $document->document_number }}</h2>
                <p class="text-sm text-muted">{{ $document->dispensation->title }}</p>
            </div>
            <x-ui.badge :status="$document->document_status">{{ $document->document_status }}</x-ui.badge>
        </div>

        <div class="bg-surface border border-line rounded-xl p-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Status Dokumen</h3>
            <ol class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                @foreach(['GENERATED' => $document->generated_at, 'SIGNED' => $document->signed_at, 'STAMPED' => $document->stamped_at, 'FINAL' => $document->finalized_at] as $label => $date)
                    <div class="rounded-lg border border-line p-3 {{ $date ? 'border-green-300 bg-green-50' : '' }}">
                        <div class="font-medium {{ $date ? 'text-green-700' : 'text-muted' }}">{{ $label }}</div>
                        <div class="text-muted mt-1">{{ $date?->format('d M Y') ?? '—' }}</div>
                    </div>
                @endforeach
            </ol>
        </div>

        <div class="bg-surface border border-line rounded-xl p-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Pengesahan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="py-2 pr-4 font-medium">Jenis</th>
                            <th class="py-2 pr-4 font-medium">Oleh</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 font-medium">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($document->endorsements as $e)
                            <tr>
                                <td class="py-2 pr-4 text-ink">{{ $e->endorsement_type }}</td>
                                <td class="py-2 pr-4 text-muted">{{ $e->user->name }}</td>
                                <td class="py-2 pr-4 text-muted">{{ $e->status }}</td>
                                <td class="py-2 text-muted">{{ $e->endorsed_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-muted text-center">Belum ada pengesahan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @if(!$document->signed_at)
                <form method="POST" action="{{ route('admin.documents.sign', $document) }}">@csrf<x-ui.button type="submit">Tandatangani</x-ui.button></form>
            @endif
            @if($document->signed_at && !$document->stamped_at)
                <form method="POST" action="{{ route('admin.documents.stamp', $document) }}">@csrf<x-ui.button type="submit">Stempel</x-ui.button></form>
            @endif
            @if($document->stamped_at && !$document->finalized_at)
                <form method="POST" action="{{ route('admin.documents.finalize', $document) }}">@csrf<x-ui.button type="submit">Finalisasi</x-ui.button></form>
            @endif
        </div>
    </div>
</x-app-layout>
