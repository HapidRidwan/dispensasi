<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">{{ $activity->title }}</h2>
                <p class="text-sm text-muted">{{ $activity->other_type ?: $activity->type->name }} · {{ $activity->date_start->format('d M Y') }}</p>
            </div>
            <x-ui.badge :status="$activity->status">{{ $activity->status }}</x-ui.badge>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-3">Detail Kegiatan</h3>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-muted">Tanggal</dt><dd class="text-ink">{{ $activity->date_start->format('d M Y') }} {{ $activity->date_end ? ' - '.$activity->date_end->format('d M Y') : '' }}</dd></div>
                        <div><dt class="text-muted">Waktu</dt><dd class="text-ink">{{ $activity->time_start ?? '-' }} - {{ $activity->time_end ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Lokasi</dt><dd class="text-ink">{{ $activity->location ?? '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-muted">Deskripsi</dt><dd class="text-ink">{{ $activity->reason }}</dd></div>
                    </dl>
                </div>

                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-3">Peserta ({{ $activity->recipients()->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                                    <th class="py-2 pr-4 font-medium">No</th>
                                    <th class="py-2 pr-4 font-medium">Nama</th>
                                    <th class="py-2 pr-4 font-medium">NIS</th>
                                    <th class="py-2 font-medium">Kelas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach($activity->recipients as $i => $r)
                                    <tr>
                                        <td class="py-2 pr-4 text-muted">{{ $i + 1 }}</td>
                                        <td class="py-2 pr-4 text-ink">{{ $r->student->user->name }}</td>
                                        <td class="py-2 pr-4 text-muted">{{ $r->student->nis }}</td>
                                        <td class="py-2 text-muted">{{ $r->student->enrollments()->where('is_active', true)->first()?->class?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-3">Penanggung Jawab</h3>
                    <ul class="text-sm space-y-2">
                        @foreach($activity->responsibles as $r)
                            <li class="flex justify-between"><span class="text-ink">{{ $r->teacher->user->name }}</span><span class="text-muted">{{ $r->responsibility_type }}</span></li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-4">Riwayat</h3>
                    <ol class="relative border-l border-line ml-2 space-y-4">
                        @foreach($activity->approvals as $approval)
                            <li class="ml-4">
                                <span class="absolute -left-[5px] mt-1.5 h-2.5 w-2.5 rounded-full {{ $approval->status === 'APPROVED' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                <div class="text-sm font-medium text-ink">{{ $approval->stage }} — {{ $approval->status }}</div>
                                <div class="text-xs text-muted">{{ $approval->reviewer->name }} · {{ $approval->reviewed_at?->format('d M Y H:i') }}</div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
