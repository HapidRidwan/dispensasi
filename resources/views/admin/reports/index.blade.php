<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-ink">Laporan</h2>
            <p class="text-sm text-muted">Ringkasan aktivitas dispensasi.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cards.stat-card label="Total Pengajuan" :value="$total" />
            @foreach($byStatus as $status => $count)
                <x-cards.stat-card :label="$status" :value="$count" />
            @endforeach
        </div>

        <div class="bg-surface border border-line rounded-xl">
            <div class="px-5 py-4 border-b border-line">
                <h3 class="text-sm font-semibold text-ink">Log Aktivitas</h3>
            </div>
            <div class="divide-y divide-line">
                @forelse($logs as $log)
                    <div class="px-5 py-3 text-sm">
                        <span class="text-ink">{{ $log->user->name }}</span>
                        <span class="text-muted">— {{ $log->description ?? $log->action }}</span>
                        @if($log->new_status)
                            <x-ui.badge :status="$log->new_status">{{ $log->new_status }}</x-ui.badge>
                        @endif
                        <span class="text-xs text-muted ml-2">{{ $log->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <x-ui.empty-state title="Belum ada aktivitas" />
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
