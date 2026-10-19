<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-ink">Pengguna</h2>
                <p class="text-sm text-muted">Kelola akun siswa, guru, dan admin.</p>
            </div>
            <x-ui.button onclick="document.getElementById('create-user').classList.remove('hidden')">Tambah Pengguna</x-ui.button>
        </div>

        <div class="flex flex-wrap gap-2 text-sm">
            @foreach(['' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru', 'admin' => 'Admin'] as $value => $label)
                <a href="{{ route('admin.users.index', $value ? ['role' => $value] : []) }}"
                   class="px-3 py-1.5 rounded-full border {{ request('role') === $value ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-background' }}">{{ $label }}</a>
            @endforeach
        </div>

        <x-data-search placeholder="Cari nama, username, atau email..." />

        <div class="bg-surface border border-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-line">
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Username</th>
                            <th class="px-5 py-3 font-medium">Email Pemulihan</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-5 py-3 text-ink">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-muted">{{ $user->username }}</td>
                                <td class="px-5 py-3 text-muted">{{ $user->email ?: '—' }}</td>
                                <td class="px-5 py-3 capitalize text-muted">{{ $user->role }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs {{ $user->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                        @csrf
                                        <button class="text-sm {{ $user->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <button onclick="document.getElementById('reset-{{ $user->id }}').classList.remove('hidden')" class="text-sm text-primary hover:underline ml-3">Reset Password</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state title="Belum ada pengguna" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $users->links() }}</div>
        </div>
    </div>

    <x-ui.modal title="Tambah Pengguna" id="create-user" class="hidden">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <x-ui.input label="Nama" name="name" required :error="$errors->first('name')" />
            <x-ui.input label="Username" name="username" required :error="$errors->first('username')" />
            <x-ui.input label="Email Pemulihan (opsional)" name="email" type="email" :error="$errors->first('email')" />
            <x-ui.select label="Role" name="role" required>
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
                <option value="admin">Admin</option>
            </x-ui.select>
            <x-ui.input label="Password" name="password" type="password" required :error="$errors->first('password')" />
            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('create-user').classList.add('hidden')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    @foreach($users as $user)
        <x-ui.modal title="Reset Password — {{ $user->name }}" id="reset-{{ $user->id }}" class="hidden">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-4">
                @csrf
                <x-ui.input label="Password Baru" name="password" type="password" required :error="$errors->first('password')" />
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" onclick="document.getElementById('reset-{{ $user->id }}').classList.add('hidden')">Batal</x-ui.button>
                    <x-ui.button type="submit">Reset</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endforeach
</x-app-layout>
