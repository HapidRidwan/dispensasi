<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Siswa</h2>
                <p class="text-sm text-muted">Kelola data siswa.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" onclick="document.getElementById('import-students').scrollIntoView({ behavior: 'smooth' })">Import Excel</x-ui.button>
                <x-ui.button onclick="document.getElementById('create-student').classList.remove('hidden')">Tambah Siswa</x-ui.button>
            </div>
        </div>

        <x-data-search placeholder="Cari nama, NIS, atau kelas..." />

        <x-data-import dataset="students" title="Import data siswa" description="Buat akun siswa dan kelas aktif sekaligus. Kolom wajib: Nama, Username, Password, NIS." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">NIS</th>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Kelas</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($students as $student)
                            <tr>
                                <td class="px-5 py-3 text-muted">{{ $student->nis }}</td>
                                <td class="px-5 py-3 text-ink">{{ $student->user->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $student->enrollments()->where('is_active', true)->first()?->class?->name ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs {{ $student->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $student->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.students.toggle-active', $student) }}" class="inline">
                                        @csrf
                                        <button class="text-sm {{ $student->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">{{ $student->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada siswa" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $students->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Siswa" id="create-student" class="{{ $errors->any() ? '' : 'hidden' }}">
        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-4">
            @csrf
            <x-ui.input label="Nama" name="name" :value="old('name')" required :error="$errors->first('name')" />
            <x-ui.input label="Username" name="username" :value="old('username')" required :error="$errors->first('username')" />
            <x-ui.input label="Email Pemulihan (opsional)" name="email" type="email" :value="old('email')" :error="$errors->first('email')" />
            <x-ui.input label="Password" name="password" type="password" required :error="$errors->first('password')" />
            <x-ui.input label="NIS" name="nis" :value="old('nis')" required :error="$errors->first('nis')" />
            <x-ui.input label="NISN" name="nisn" :value="old('nisn')" :error="$errors->first('nisn')" />
            <x-ui.select label="Kelas" name="class_id">
                <option value="">— Pilih Kelas —</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Tahun Ajaran" name="academic_year" :value="old('academic_year', date('Y').'/'.(date('Y') + 1))" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-student').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
