<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Penugasan Guru</h2>
                <p class="text-sm text-muted">Penugasan wali kelas mengikuti tahun ajaran kelas.</p>
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
                            <th class="px-5 py-3 font-medium">Kelas</th>
                            <th class="px-5 py-3 font-medium">Tahun Ajaran</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($assignments as $a)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $a->teacher->user->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $a->assignment_type }}</td>
                                <td class="px-5 py-3 text-muted">{{ $a->class?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-muted">{{ $a->class?->academic_year ?? '-' }}</td>
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
                @foreach(\App\Models\Teacher::with('user')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select label="Jenis Tugas" name="assignment_type" required>
                <option value="HOMEROOM">Wali Kelas</option>
                <option value="OTHER">Lainnya</option>
            </x-ui.select>
            <x-ui.select label="Kelas (tahun ajaran)" name="class_id">
                <option value="">— Tidak ada —</option>
                @foreach(\App\Models\SchoolClass::where('is_active', true)->get() as $class)
                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->academic_year }})</option>
                @endforeach
            </x-ui.select>
            @error('class_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-assignment').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
