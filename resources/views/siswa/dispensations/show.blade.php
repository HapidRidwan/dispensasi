<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">{{ $dispensation->title }}</h2>
                <p class="text-sm text-muted">{{ $dispensation->other_type ?: $dispensation->type->name }} · {{ $dispensation->date_start->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array($dispensation->status, ['APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED', 'COMPLETED'], true))
                    <a href="{{ route('dispensations.print', $dispensation) }}" target="_blank" class="text-sm font-medium text-primary hover:underline">Cetak Kartu Izin</a>
                @endif
                <x-ui.badge :status="$dispensation->status">{{ $dispensation->status }}</x-ui.badge>
            </div>
        </div>

        <div class="bg-surface border border-line rounded-xl p-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Detail</h3>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-muted">Tanggal</dt><dd class="text-ink">{{ $dispensation->date_start->format('d M Y') }} {{ $dispensation->date_end ? ' - '.$dispensation->date_end->format('d M Y') : '' }}</dd></div>
                <div><dt class="text-muted">Waktu</dt><dd class="text-ink">{{ $dispensation->time_start ?? '-' }} - {{ $dispensation->time_end ?? '-' }}</dd></div>
                <div><dt class="text-muted">Lokasi</dt><dd class="text-ink">{{ $dispensation->location ?? '-' }}</dd></div>
                <div><dt class="text-muted">Tujuan</dt><dd class="text-ink">{{ $dispensation->destination ?? '-' }}</dd></div>
                <div><dt class="text-muted">Guru Piket</dt><dd class="text-ink">{{ $dispensation->picketAssignment?->teacher?->user?->name ?? 'Menunggu penetapan admin' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-muted">Alasan</dt><dd class="text-ink">{{ $dispensation->reason }}</dd></div>
                @if($dispensation->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-muted">Alasan Penolakan</dt><dd class="text-red-600">{{ $dispensation->rejection_reason }}</dd></div>
                @endif
            </dl>
        </div>

        @if($dispensation->document)
            <div class="bg-surface border border-line rounded-xl p-5">
                <h3 class="text-sm font-semibold text-ink mb-3">Dokumen</h3>
                <div class="text-sm">
                    <div class="text-ink">{{ $dispensation->document->document_number }}</div>
                    <div class="text-muted text-xs mt-1">Status: {{ $dispensation->document->document_status }}</div>
                </div>
            </div>
        @endif

        <div class="bg-surface border border-line rounded-xl p-5">
            <h3 class="text-sm font-semibold text-ink mb-4">Riwayat</h3>
            <ol class="relative border-l border-line ml-2 space-y-4">
                @foreach($dispensation->approvals as $approval)
                    <li class="ml-4">
                        <span class="absolute -left-[5px] mt-1.5 h-2.5 w-2.5 rounded-full {{ $approval->status === 'APPROVED' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <div class="text-sm font-medium text-ink">{{ $approval->stage }} — {{ $approval->status }}</div>
                        <div class="text-xs text-muted">{{ $approval->reviewer->name }} · {{ $approval->reviewed_at?->format('d M Y H:i') }}</div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</x-app-layout>
