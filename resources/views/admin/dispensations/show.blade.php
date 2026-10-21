<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">{{ $dispensation->title }}</h2>
                <p class="text-sm text-muted">Review pengajuan dispensasi.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array($dispensation->status, ['APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED', 'COMPLETED'], true))
                    <a href="{{ route('dispensations.print', $dispensation) }}" target="_blank" class="text-sm font-medium text-primary hover:underline">Cetak Kartu Izin</a>
                @endif
                <x-ui.badge :status="$dispensation->status">{{ $dispensation->status }}</x-ui.badge>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-ink">Informasi Pengajuan</h3>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-muted">Pengaju</dt><dd class="text-ink">{{ $dispensation->applicant->name }}</dd></div>
                        <div><dt class="text-muted">Jenis</dt><dd class="text-ink">{{ $dispensation->other_type ?: $dispensation->type->name }}</dd></div>
                        <div><dt class="text-muted">Tanggal Mulai</dt><dd class="text-ink">{{ $dispensation->date_start->format('d M Y') }}</dd></div>
                        <div><dt class="text-muted">Tanggal Selesai</dt><dd class="text-ink">{{ $dispensation->date_end?->format('d M Y') ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Waktu</dt><dd class="text-ink">{{ $dispensation->time_start ?? '-' }} - {{ $dispensation->time_end ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Lokasi</dt><dd class="text-ink">{{ $dispensation->location ?? '-' }}</dd></div>
                        <div><dt class="text-muted">Guru Piket</dt><dd class="text-ink">{{ $dispensation->picketAssignment?->teacher?->user?->name ?? '-' }}{{ $dispensation->picketAssignment ? ' · '.$dispensation->picketAssignment->day_of_week.' '.substr($dispensation->picketAssignment->start_time, 0, 5).'-'.substr($dispensation->picketAssignment->end_time, 0, 5) : '' }}</dd></div>
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

                @if($dispensation->responsibles->isNotEmpty())
                    <div class="bg-surface border border-line rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-ink mb-3">Penanggung Jawab & Pembimbing</h3>
                        <ul class="text-sm space-y-2">
                            @foreach($dispensation->responsibles as $r)
                                <li class="flex justify-between"><span class="text-ink">{{ $r->teacher->user->name }}</span><span class="text-muted">{{ $r->responsibility_type }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($dispensation->attachments->isNotEmpty())
                    <div class="bg-surface border border-line rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-ink mb-3">Lampiran</h3>
                        <ul class="text-sm space-y-2">
                            @foreach($dispensation->attachments as $a)
                                <li><a href="{{ asset('storage/'.$a->file_path) }}" target="_blank" class="text-primary hover:underline">{{ $a->file_name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
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
                                @if($approval->notes)<div class="text-xs text-muted mt-1">{{ $approval->notes }}</div>@endif
                            </li>
                        @empty
                            <li class="ml-4 text-sm text-muted">Belum ada riwayat verifikasi.</li>
                        @endforeach
                    </ol>
                </div>

                @if(in_array($dispensation->status, ['SUBMITTED', 'TEACHER_VERIFIED', 'ADMIN_REVIEW']))
                    <div class="bg-surface border border-line rounded-xl p-5 space-y-3">
                        <x-ui.button class="w-full" onclick="document.getElementById('approve-modal').classList.remove('hidden')">Setujui</x-ui.button>
                        <x-ui.button variant="danger" class="w-full" onclick="document.getElementById('reject-modal').classList.remove('hidden')">Tolak</x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-ui.modal title="Setujui Pengajuan" id="approve-modal" class="hidden">
        <form method="POST" action="{{ route('admin.dispensations.approve', $dispensation) }}" class="space-y-4">
            @csrf
            <x-ui.select label="Guru Piket (opsional)" name="picket_assignment_id" :error="$errors->first('picket_assignment_id')">
                <option value="">Tidak ditentukan</option>
                @foreach($picketAssignments as $assignment)
                    <option value="{{ $assignment->id }}" @selected(old('picket_assignment_id', $dispensation->picket_assignment_id) == $assignment->id)>
                        {{ $assignment->teacher->user->name }} · {{ $assignment->day_of_week }} · {{ substr($assignment->start_time, 0, 5) }}–{{ substr($assignment->end_time, 0, 5) }}
                    </option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Catatan (opsional)" name="notes" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('approve-modal').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Setujui</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal title="Tolak Pengajuan" id="reject-modal" class="hidden">
        <form method="POST" action="{{ route('admin.dispensations.reject', $dispensation) }}" class="space-y-4">
            @csrf
            <x-ui.input label="Alasan Penolakan" name="notes" required :error="$errors->first('notes')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('reject-modal').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger">Tolak</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
