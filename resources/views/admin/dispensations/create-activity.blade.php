<x-app-layout>
    @php
        $otherTypeId = $types->first(fn ($type) => mb_strtolower(trim($type->name)) === 'lainnya')?->id;
        $isOtherTypeSelected = $otherTypeId && (int) old('type_id') === $otherTypeId;
    @endphp
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-ink">Buat Kegiatan Formal</h2>
            <p class="text-sm text-muted">Kegiatan lintas kelas yang dibuat admin langsung disetujui dan diinformasikan kepada siswa serta guru terkait.</p>
        </div>

        <form method="POST" action="{{ route('admin.activities.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Data Kegiatan</h3>
                <div x-data="{ isOtherType: @js($isOtherTypeSelected) }">
                    <x-ui.select label="Jenis Kegiatan" name="type_id" required :error="$errors->first('type_id')" @change="isOtherType = $event.target.selectedOptions[0].dataset.other === 'true'">
                        <option value="">Pilih jenis kegiatan</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" data-other="{{ mb_strtolower(trim($type->name)) === 'lainnya' ? 'true' : 'false' }}" @selected(old('type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <div x-show="isOtherType" x-cloak>
                        <x-ui.input class="mt-3" label="Nama kategori lainnya" name="other_type" :value="old('other_type')" x-bind:required="isOtherType" x-bind:disabled="!isOtherType" :error="$errors->first('other_type')" />
                    </div>
                </div>
                <x-ui.input label="Nama Kegiatan" name="title" :value="old('title')" required :error="$errors->first('title')" />
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Tanggal Mulai" name="date_start" type="date" :value="old('date_start')" required :error="$errors->first('date_start')" />
                    <x-ui.input label="Tanggal Selesai" name="date_end" type="date" :value="old('date_end')" :error="$errors->first('date_end')" />
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Waktu Mulai" name="time_start" type="time" :value="old('time_start')" />
                    <x-ui.input label="Waktu Selesai" name="time_end" type="time" :value="old('time_end')" />
                </div>
                <x-ui.input label="Lokasi" name="location" :value="old('location')" />
                <x-ui.input label="Tujuan" name="destination" :value="old('destination')" />
                <x-ui.input label="Deskripsi / Keperluan" name="reason" :value="old('reason')" required :error="$errors->first('reason')" />
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Penanggung Jawab</h3>
                <x-ui.select label="Guru Penanggung Jawab" name="responsibles[0][teacher_id]" required :error="$errors->first('responsibles.0.teacher_id')">
                    <option value="">Pilih guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('responsibles.0.teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </x-ui.select>
                <input type="hidden" name="responsibles[0][responsibility_type]" value="PENANGGUNG_JAWAB">
                @error('responsibles')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Peserta</h3>
                <x-ui.select label="Pilih Siswa" name="student_ids[]" required multiple class="h-48" :error="$errors->first('student_ids')">
                    @foreach($students as $student)
                        @php
                            $activeEnrollment = $student->enrollments->first(fn ($enrollment) => $enrollment->is_active);
                        @endphp
                        <option value="{{ $student->id }}" @selected(in_array($student->id, (array) old('student_ids', [])))>{{ $student->user->name }} ({{ $student->nis }}) · {{ $activeEnrollment?->class?->name ?? 'Tanpa kelas' }}</option>
                    @endforeach
                </x-ui.select>
                @error('student_ids.*')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Dokumen Kegiatan</h3>
                <x-ui.input label="File" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" help="Surat undangan / surat tugas. Maks 5MB." :error="$errors->first('file')" />
            </div>

            <x-ui.button type="submit" class="w-full">Buat dan Setujui Kegiatan</x-ui.button>
        </form>
    </div>
</x-app-layout>
