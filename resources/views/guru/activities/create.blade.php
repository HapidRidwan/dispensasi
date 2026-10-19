<x-app-layout>
    @php
        $activityTypes = \App\Models\DispensationType::where('category', 'ACTIVITY')->where('is_active', true)->get();
        $otherTypeId = $activityTypes->first(fn ($type) => mb_strtolower(trim($type->name)) === 'lainnya')?->id;
        $isOtherTypeSelected = $otherTypeId && (int) old('type_id') === $otherTypeId;
    @endphp
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-ink">Buat Kegiatan Formal</h2>
            <p class="text-sm text-muted">Kegiatan dengan peserta lintas kelas.</p>
        </div>

        <form method="POST" action="{{ route('guru.activities.store') }}" enctype="multipart/form-data" class="space-y-6">
            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Data Kegiatan</h3>
                <div x-data="{ isOtherType: @js($isOtherTypeSelected) }">
                    <x-ui.select label="Jenis Kegiatan" name="type_id" required :error="$errors->first('type_id')" @change="isOtherType = $event.target.selectedOptions[0].dataset.other === 'true'">
                        <option value="">Pilih jenis kegiatan</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->id }}" data-other="{{ mb_strtolower(trim($type->name)) === 'lainnya' ? 'true' : 'false' }}" @selected(old('type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <div x-show="isOtherType" x-cloak>
                        <x-ui.input class="mt-3" label="Nama kategori lainnya" name="other_type" :value="old('other_type')" x-bind:required="isOtherType" x-bind:disabled="!isOtherType" :error="$errors->first('other_type')" />
                    </div>
                </div>
                <x-ui.input label="Nama Kegiatan" name="title" required :error="$errors->first('title')" />
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Tanggal Mulai" name="date_start" type="date" required :error="$errors->first('date_start')" />
                    <x-ui.input label="Tanggal Selesai" name="date_end" type="date" :error="$errors->first('date_end')" />
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Waktu Mulai" name="time_start" type="time" />
                    <x-ui.input label="Waktu Selesai" name="time_end" type="time" />
                </div>
                <x-ui.input label="Lokasi" name="location" />
                <x-ui.input label="Deskripsi / Keperluan" name="reason" required :error="$errors->first('reason')" />
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Penanggung Jawab & Pembimbing</h3>
                <div class="grid sm:grid-cols-3 gap-4">
                    <x-ui.select label="Penanggung Jawab" name="responsibles[0][teacher_id]" required>
                        @foreach(\App\Models\Teacher::with('user')->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Pembimbing" name="responsibles[1][teacher_id]">
                        <option value="">— Tidak ada —</option>
                        @foreach(\App\Models\Teacher::with('user')->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Pendamping" name="responsibles[2][teacher_id]">
                        <option value="">— Tidak ada —</option>
                        @foreach(\App\Models\Teacher::with('user')->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <input type="hidden" name="responsibles[0][responsibility_type]" value="PENANGGUNG_JAWAB">
                <input type="hidden" name="responsibles[1][responsibility_type]" value="PEMBIMBING">
                <input type="hidden" name="responsibles[2][responsibility_type]" value="PENDAMPING">
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4" x-data="{ q: '', students: @js(\App\Models\Student::with('user')->get()->map(fn ($s) => ['id' => $s->id, 'name' => $s->user->name, 'nis' => $s->nis, 'class' => $s->enrollments()->where('is_active', true)->first()?->class?->name])), selected: [] }">
                <h3 class="text-sm font-semibold text-ink">Peserta</h3>
                <x-ui.input label="Cari Siswa" x-model="q" placeholder="Nama atau NIS" />
                <div class="max-h-48 overflow-y-auto border border-line rounded-lg divide-y divide-line">
                    <template x-for="s in students.filter(x => !selected.some(y => y.id === x.id) && (x.name.toLowerCase().includes(q.toLowerCase()) || x.nis.includes(q)))" :key="s.id">
                        <button type="button" @click="selected.push(s)" class="w-full text-left px-4 py-2 text-sm hover:bg-background flex justify-between">
                            <span x-text="s.name"></span>
                            <span class="text-muted" x-text="s.nis + ' · ' + (s.class ?? '-')"></span>
                        </button>
                    </template>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                                <th class="py-2 pr-4 font-medium">No</th>
                                <th class="py-2 pr-4 font-medium">Nama</th>
                                <th class="py-2 pr-4 font-medium">NIS</th>
                                <th class="py-2 pr-4 font-medium">Kelas</th>
                                <th class="py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            <template x-for="(s, i) in selected" :key="s.id">
                                <tr>
                                    <td class="py-2 pr-4 text-muted" x-text="i + 1"></td>
                                    <td class="py-2 pr-4 text-ink" x-text="s.name"></td>
                                    <td class="py-2 pr-4 text-muted" x-text="s.nis"></td>
                                    <td class="py-2 pr-4 text-muted" x-text="s.class ?? '-'"></td>
                                    <td class="py-2 text-right">
                                        <button type="button" @click="selected = selected.filter(x => x.id !== s.id)" class="text-red-600 hover:underline">Hapus</button>
                                        <input type="hidden" name="student_ids[]" :value="s.id">
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="selected.length === 0"><td colspan="5" class="py-4 text-muted text-center">Belum ada peserta.</td></tr>
                        </tbody>
                    </table>
                </div>
                @error('student_ids')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Dokumen Kegiatan</h3>
                <x-ui.input label="File" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" help="Surat undangan / surat tugas. Maks 5MB." :error="$errors->first('file')" />
            </div>

            <x-ui.button type="submit" class="w-full">Kirim Kegiatan</x-ui.button>
        </form>
    </div>
</x-app-layout>
