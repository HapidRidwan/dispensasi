<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Daftar Penandatangan</h2>
                <p class="text-sm text-muted">Kelola pejabat sekolah dan pengurus organisasi yang dapat dipilih pada formulir dispensasi.</p>
            </div>
            <a href="{{ route('admin.documents.index') }}" class="text-sm text-primary hover:underline">Kembali ke Dokumen</a>
        </div>

        <form method="POST" action="{{ route('admin.school-signatories.store') }}" class="bg-surface border border-line rounded-xl p-5 space-y-4">
            @csrf
            <h3 class="text-sm font-semibold text-ink">Tambah Nama</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-ui.input label="Jabatan" name="position" placeholder="Contoh: Kepala Sekolah, Wakasek, Ketua Organisasi" required :value="old('position')" :error="$errors->first('position')" />
                <x-ui.input label="Nama" name="name" required :value="old('name')" :error="$errors->first('name')" />
            </div>
            <x-ui.button type="submit">Tambah Penandatangan</x-ui.button>
        </form>

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Jabatan</th>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($schoolSignatories as $schoolSignatory)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $schoolSignatory->position }}</td>
                                <td class="px-5 py-3 text-muted">{{ $schoolSignatory->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $schoolSignatory->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.school-signatories.toggle-active', $schoolSignatory) }}">
                                        @csrf
                                        <button type="submit" class="text-primary hover:underline">{{ $schoolSignatory->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-ui.empty-state title="Belum ada penandatangan" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $schoolSignatories->links() }}</div>
        </div>
    </div>
</x-app-layout>
