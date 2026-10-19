<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">{{ $dispensation->title }}</h2>
                <p class="text-sm text-muted">{{ $dispensation->other_type ?: $dispensation->type->name }} · {{ $dispensation->date_start->format('d M Y') }}</p>
            </div>
            <x-ui.badge :status="$dispensation->status">{{ $dispensation->status }}</x-ui.badge>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-3">Detail</h3>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-muted">Tanggal</dt><dd class="text-ink">{{ $dispensation->date_start->format('d M Y') }} {{ $dispensation->date_end ? ' - '.$dispensation->date_end->format('d M Y') : '' }}</dd></div>
                        <div><dt class="text-muted">Waktu</dt><dd class="text-ink">{{ $dispensation->time_start ?? '-' }} - {{ $dispensation->time_end ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Lokasi</dt><dd class="text-ink">{{ $dispensation->location ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Tujuan</dt><dd class="text-ink">{{ $dispensation->destination ?? '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-muted">Alasan</dt><dd class="text-ink">{{ $dispensation->reason }}</dd></div>
                    </dl>
                </div>

                <div class="bg-surface border border-line rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-ink mb-3">Peserta</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                                    <th class="py-2 pr-4 font-medium">Nama</th>
                                    <th class="py-2 pr-4 font-medium">NIS</th>
                                    <th class="py-2 font-medium">Kelas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse($dispensation->recipients as $r)
                                    <tr>
                                        <td class="py-2 pr-4 text-ink">{{ $r->student->user->name }}</td>
                                        <td class="py-2 pr-4 text-muted">{{ $r->student->nis }}</td>
                                        <td class="py-2 text-muted">{{ $r->student->enrollments()->where('is_active', true)->first()?->class?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted text-center">Tidak ada peserta.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
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

                @if($dispensation->status === 'SUBMITTED')
                    <div class="bg-surface border border-line rounded-xl p-5 space-y-3">
                        <x-ui.button class="w-full" onclick="document.getElementById('verify-modal').classList.remove('hidden')">Verifikasi</x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-ui.modal title="Verifikasi Pengajuan" id="verify-modal" class="hidden">
        <form method="POST" action="{{ route('guru.dispensations.verify', $dispensation) }}" class="space-y-4">
            @csrf
            <x-ui.select label="Keputusan" name="approved" required>
                <option value="1">Setujui</option>
                <option value="0">Tolak</option>
            </x-ui.select>
            <x-ui.input label="Catatan" name="notes" :error="$errors->first('notes')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('verify-modal').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
