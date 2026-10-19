<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Kelas</h2>
                <p class="text-sm text-muted">Kelola data kelas.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" onclick="document.getElementById('import-classes').scrollIntoView({ behavior: 'smooth' })">Import Excel</x-ui.button>
                <x-ui.button onclick="document.getElementById('create-class').classList.remove('hidden')">Tambah Kelas</x-ui.button>
            </div>
        </div>

        <x-data-search placeholder="Cari nama, tingkat, atau tahun ajaran..." />
        <x-data-import dataset="classes" title="Import data kelas" description="Kolom wajib: Nama Kelas, Tingkat, dan Tahun Ajaran." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Tingkat</th>
                            <th class="px-5 py-3 font-medium">Jurusan</th>
                            <th class="px-5 py-3 font-medium">Tahun Ajaran</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($classes as $class)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $class->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $class->grade }}</td>
                                <td class="px-5 py-3 text-muted">{{ $class->major ?? '-' }}</td>
                                <td class="px-5 py-3 text-muted">{{ $class->academic_year }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs {{ $class->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $class->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada kelas" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $classes->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Kelas" id="create-class" class="{{ $errors->any() ? '' : 'hidden' }}">
        <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-4">
            @csrf
            <x-ui.input label="Nama Kelas" name="name" :value="old('name')" required :error="$errors->first('name')" />
            <x-ui.input label="Tingkat" name="grade" :value="old('grade')" required :error="$errors->first('grade')" />
            <x-ui.input label="Jurusan" name="major" :value="old('major')" :error="$errors->first('major')" />
            <x-ui.input label="Tahun Ajaran" name="academic_year" required :value="old('academic_year', date('Y').'/'.(date('Y') + 1))" :error="$errors->first('academic_year')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-class').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
