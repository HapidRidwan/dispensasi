<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Penugasan Guru</h2>
                <p class="text-sm text-muted">Atur wali kelas, jadwal guru piket, dan penugasan lainnya.</p>
            </div>
            <x-ui.button onclick="document.getElementById('create-assignment').classList.remove('hidden')">Tambah Penugasan</x-ui.button>
        </div>

        <x-data-search placeholder="Cari guru, kelas, jenis tugas, atau tahun ajaran..." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Guru</th>
                            <th class="px-5 py-3 font-medium">Tugas</th>
                            <th class="px-5 py-3 font-medium">Kelas / Jadwal</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($assignments as $a)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $a->teacher->user->name }}</td>
                                <td class="px-5 py-3 text-muted">
                                    {{ match($a->assignment_type) { 'HOMEROOM' => 'Wali Kelas', 'DUTY' => 'Guru Piket', default => 'Lainnya' } }}
                                </td>
                                <td class="px-5 py-3 text-muted">
                                    @if($a->assignment_type === 'DUTY')
                                        {{ $a->day_of_week }} · {{ substr($a->start_time, 0, 5) }}–{{ substr($a->end_time, 0, 5) }}
                                    @else
                                        {{ $a->class?->name ?? '-' }}{{ $a->class ? ' · '.$a->class->academic_year : '' }}
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs {{ $a->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $a->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $a->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.teacher-assignments.destroy', $a) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-sm text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state title="Belum ada penugasan" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $assignments->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Penugasan" id="create-assignment" class="hidden">
        <form method="POST" action="{{ route('admin.teacher-assignments.store') }}" class="space-y-4">
            @csrf
            <x-ui.select label="Guru" name="teacher_id" required>
                <option value="">Pilih guru</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                @endforeach
            </x-ui.select>
            <div x-data="{ assignmentType: @js(old('assignment_type', 'HOMEROOM')) }">
                <x-ui.select label="Jenis Tugas" name="assignment_type" required x-model="assignmentType">
                <option value="HOMEROOM">Wali Kelas</option>
                <option value="DUTY">Guru Piket</option>
                <option value="OTHER">Lainnya</option>
                </x-ui.select>
                <div x-show="assignmentType === 'HOMEROOM'" x-cloak>
                    <x-ui.select class="mt-4" label="Kelas (tahun ajaran)" name="class_id">
                        <option value="">Pilih kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }} ({{ $class->academic_year }})</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div x-show="assignmentType === 'DUTY'" x-cloak class="mt-4 space-y-4">
                    <x-ui.select label="Hari Piket" name="day_of_week">
                        <option value="">Pilih hari</option>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                            <option value="{{ $day }}" @selected(old('day_of_week') === $day)>{{ $day }}</option>
                        @endforeach
                    </x-ui.select>
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.input label="Mulai" name="start_time" type="time" :value="old('start_time')" />
                        <x-ui.input label="Selesai" name="end_time" type="time" :value="old('end_time')" />
                    </div>
                    @error('day_of_week')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('start_time')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('end_time')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            @error('class_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-assignment').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
