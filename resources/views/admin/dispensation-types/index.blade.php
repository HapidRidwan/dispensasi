<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Jenis Dispensasi</h2>
                <p class="text-sm text-muted">Kelola kategori dispensasi siswa dan kegiatan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" onclick="document.getElementById('import-dispensation-types').scrollIntoView({ behavior: 'smooth' })">Import Excel</x-ui.button>
                <x-ui.button onclick="document.getElementById('create-type').classList.remove('hidden')">Tambah Jenis</x-ui.button>
            </div>
        </div>

        <x-data-search placeholder="Cari nama kategori atau deskripsi..." />
        <x-data-import dataset="dispensation-types" title="Import jenis dispensasi" description="Kolom wajib: Nama dan Kategori (Siswa atau Kegiatan)." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Kategori</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($types as $type)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $type->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $type->category }}</td>
                                <td class="px-5 py-3">
                                    <form method="POST" action="{{ route('admin.dispensation-types.toggle-active', $type) }}">
                                        @csrf
                                        <button class="text-sm {{ $type->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button onclick="document.getElementById('edit-type-{{ $type->id }}').classList.remove('hidden')" class="text-sm text-primary hover:underline">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-ui.empty-state title="Belum ada jenis dispensasi" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $types->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Jenis Dispensasi" id="create-type" class="{{ $errors->any() ? '' : 'hidden' }}">
        <form method="POST" action="{{ route('admin.dispensation-types.store') }}" class="space-y-4">
            @csrf
            <x-ui.input label="Nama" name="name" :value="old('name')" required :error="$errors->first('name')" />
            <x-ui.select label="Kategori" name="category" required :error="$errors->first('category')">
                <option value="STUDENT" @selected(old('category', 'STUDENT') === 'STUDENT')>Siswa</option>
                <option value="ACTIVITY" @selected(old('category') === 'ACTIVITY')>Kegiatan</option>
            </x-ui.select>
            <x-ui.input label="Deskripsi" name="description" :value="old('description')" :error="$errors->first('description')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-type').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    @foreach($types as $type)
        <x-ui.modal title="Edit Jenis — {{ $type->name }}" id="edit-type-{{ $type->id }}" class="hidden">
            <form method="POST" action="{{ route('admin.dispensation-types.update', $type) }}" class="space-y-4">
                @csrf @method('PUT')
                <x-ui.input label="Nama" name="name" :value="$type->name" required :error="$errors->first('name')" />
                <x-ui.select label="Kategori" name="category" required>
                    <option value="STUDENT" @selected($type->category === 'STUDENT')>Siswa</option>
                    <option value="ACTIVITY" @selected($type->category === 'ACTIVITY')>Kegiatan</option>
                </x-ui.select>
                <x-ui.input label="Deskripsi" name="description" :value="$type->description" />
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" onclick="document.getElementById('edit-type-{{ $type->id }}').classList.add('hidden')">Batal</x-ui.button>
                    <x-ui.button type="submit">Simpan</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endforeach
</x-app-layout>
