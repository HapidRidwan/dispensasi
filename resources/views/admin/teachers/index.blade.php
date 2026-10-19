<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Guru</h2>
                <p class="text-sm text-muted">Kelola data guru.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" onclick="document.getElementById('import-teachers').scrollIntoView({ behavior: 'smooth' })">Import Excel</x-ui.button>
                <x-ui.button onclick="document.getElementById('create-teacher').classList.remove('hidden')">Tambah Guru</x-ui.button>
            </div>
        </div>

        <x-data-search placeholder="Cari nama, NIP, atau jabatan..." />
        <x-data-import dataset="teachers" title="Import data guru" description="Buat akun guru bersamaan dengan profilnya. Kolom wajib: Nama, Username, Password." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">NIP</th>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Jabatan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($teachers as $teacher)
                            <tr>
                                <td class="px-5 py-3 text-muted">{{ $teacher->nip ?? '-' }}</td>
                                <td class="px-5 py-3 text-ink">{{ $teacher->user->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $teacher->position ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs {{ $teacher->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $teacher->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.teachers.toggle-active', $teacher) }}" class="inline">
                                        @csrf
                                        <button class="text-sm {{ $teacher->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">{{ $teacher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="Belum ada guru" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $teachers->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Guru" id="create-teacher" class="{{ $errors->any() ? '' : 'hidden' }}">
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-4">
            @csrf
            <x-ui.input label="Nama" name="name" :value="old('name')" required :error="$errors->first('name')" />
            <x-ui.input label="Username" name="username" :value="old('username')" required :error="$errors->first('username')" />
            <x-ui.input label="Email Pemulihan (opsional)" name="email" type="email" :value="old('email')" :error="$errors->first('email')" />
            <x-ui.input label="Password" name="password" type="password" required :error="$errors->first('password')" />
            <x-ui.input label="NIP" name="nip" :value="old('nip')" :error="$errors->first('nip')" />
            <x-ui.input label="Jabatan" name="position" :value="old('position')" :error="$errors->first('position')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-teacher').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-app-layout>
