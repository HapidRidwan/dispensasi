<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-ink">Notifikasi</h2>
            <x-data-search placeholder="Cari judul, pesan, atau tipe notifikasi..." />
        </div>

        <div class="bg-surface border border-line rounded-xl divide-y divide-line">
            @forelse($notifications as $n)
                <div class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-ink">{{ $n->title }}</div>
                            <div class="text-sm text-muted mt-0.5">{{ $n->message }}</div>
                            <div class="text-xs text-muted mt-1">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                        @unless($n->is_read)
                            <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-accent"></span>
                        @endunless
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada notifikasi" description="Notifikasi terbaru akan muncul di sini." />
            @endforelse
        </div>

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
