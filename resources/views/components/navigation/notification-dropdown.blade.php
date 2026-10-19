@php
    $unread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
    $latest = \App\Models\Notification::where('user_id', auth()->id())->latest()->limit(5)->get();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="relative rounded-xl p-2.5 text-muted transition hover:bg-blue-50 hover:text-primary" aria-label="Notifikasi" :aria-expanded="open.toString()">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span id="notif-count" class="absolute right-0.5 top-0.5 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold text-white ring-2 ring-white {{ $unread > 0 ? '' : 'hidden' }}">{{ $unread }}</span>
    </button>

    <div x-show="open" x-transition class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-line bg-white shadow-xl" @click.outside="open = false" x-cloak>
        <div id="notif-list" class="max-h-80 overflow-y-auto">
            @forelse($latest as $n)
                <a href="#" class="block border-b border-line px-4 py-3 text-sm transition hover:bg-slate-50">
                    <div class="font-medium text-ink">{{ $n->title }}</div>
                    <div class="mt-1 text-xs text-muted">{{ $n->created_at->diffForHumans() }}</div>
                </a>
            @empty
                <div class="px-4 py-6 text-sm text-muted text-center">Belum ada notifikasi.</div>
            @endforelse
        </div>
        <a href="{{ route(auth()->user()->role === 'admin' ? 'admin.notifications.index' : (auth()->user()->role === 'guru' ? 'guru.notifications.index' : 'siswa.notifications.index')) }}" class="block border-t border-line px-4 py-3 text-center text-sm font-medium text-primary transition hover:bg-blue-50">Lihat semua</a>
    </div>
</div>
