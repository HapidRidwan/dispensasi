<x-app-layout>
    @php
        $user = auth()->user();
        $student = $user->student;
        $enrollment = $student?->enrollments()->with('class')->where('is_active', true)->latest('start_date')->first();
        $teacher = $user->teacher;
    @endphp

    <div class="max-w-3xl mx-auto space-y-5">
        <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm">
            <div class="bg-gradient-to-r from-primary to-primary-dark px-5 py-6 text-white sm:px-7">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/icon/icon.png') }}" alt="" class="h-14 w-14 rounded-2xl border border-white/20 object-cover">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-white/70">Profil {{ ucfirst($user->role) }}</p>
                        <h2 class="mt-1 text-xl font-semibold">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-white/75">Kelola informasi akun dan keamanan Anda.</p>
                    </div>
                </div>
            </div>

            @if($user->role === 'admin')
                @include('profile.partials.admin')
            @elseif($user->role === 'guru')
                @include('profile.partials.guru', ['teacher' => $teacher])
            @else
                @include('profile.partials.siswa', ['student' => $student, 'enrollment' => $enrollment])
            @endif
        </div>

        @if($user->role === 'admin' && ! $user->two_factor_confirmed_at)
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-start gap-3">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong>Perhatian:</strong> Akun Admin wajib mengaktifkan dan mengkonfirmasi
                    Two-Factor Authentication sebelum dapat mengakses area admin.
                    Silakan aktifkan 2FA di bagian Keamanan di bawah.
                </div>
            </div>
        @endif

        {{-- Informasi Akun --}}
        <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-ink">Informasi Akun</h3>
            <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <x-ui.input label="Nama" name="name" :value="$user->name" required :error="$errors->first('name')" />
                <x-ui.input label="Username" name="username" :value="$user->username" required autocomplete="username" :error="$errors->first('username')" />
                <x-ui.input label="Email Pemulihan (opsional)" name="email" type="email" :value="$user->email" autocomplete="email" :error="$errors->first('email')" />
                <p class="-mt-2 text-xs text-muted">Masuk dengan username dan password. Email hanya digunakan jika Anda ingin menerima tautan pemulihan.</p>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </form>
        </div>

        {{-- Ubah Password --}}
        <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-ink">Ubah Password</h3>
            <form method="POST" action="{{ route('user-password.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <x-ui.input label="Password Saat Ini" name="current_password" type="password" required :error="$errors->first('current_password')" />
                <x-ui.input label="Password Baru" name="password" type="password" required :error="$errors->first('password')" />
                <x-ui.input label="Konfirmasi Password Baru" name="password_confirmation" type="password" required />
                <x-ui.button type="submit">Ubah Password</x-ui.button>
            </form>
        </div>

        {{-- Keamanan / 2FA (admin only) --}}
        @if($user->role === 'admin')
            <div class="bg-surface border border-line rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-ink">Keamanan — Two-Factor Authentication</h3>
                    @if($user->two_factor_confirmed_at)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            Belum Aktif
                        </span>
                    @endif
                </div>

                <p class="text-sm text-muted mb-4">
                    Tambahkan lapisan keamanan ekstra dengan menggunakan aplikasi authenticator
                    (Google Authenticator, Authy, dll.) untuk membuat kode OTP setiap login.
                </p>

                @livewire('profile.two-factor-authentication-form')
            </div>
        @endif

        {{-- Notifikasi Perangkat --}}
        <div class="bg-surface border border-line rounded-xl p-5" x-data="pushSettings()">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Notifikasi Perangkat (Push)</h3>
                    <p class="text-sm text-muted mt-0.5" x-text="status"></p>
                    <p class="text-xs text-muted mt-0.5">Terima notifikasi saat tab browser tidak aktif.</p>
                </div>
                <x-ui.button type="button" variant="secondary" @click="toggle()" x-text="enabled ? 'Nonaktifkan' : 'Aktifkan'"></x-ui.button>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-red-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-ink">Keluar dari akun</h3>
                <p class="mt-1 text-sm text-muted">Akhiri sesi Anda di perangkat ini.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500/30 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>

    </div>

    <script>
        function pushSettings() {
            return {
                enabled: Notification.permission === 'granted',
                status: Notification.permission === 'granted' ? 'Notifikasi push aktif.' : 'Notifikasi push belum aktif.',
                async toggle() {
                    if (this.enabled) {
                        this.enabled = false;
                        this.status = 'Notifikasi push dinonaktifkan.';
                        return;
                    }
                    const permission = await Notification.requestPermission();
                    if (permission !== 'granted') {
                        this.status = 'Izin notifikasi ditolak oleh browser.';
                        return;
                    }
                    try {
                        const reg = await navigator.serviceWorker.register('/service-worker.js');
                        const sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: '{{ $vapidPublicKey ?? "" }}'
                        });
                        const keys = sub.toJSON().keys;
                        await fetch('{{ route('push-subscriptions.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                endpoint: sub.endpoint,
                                p256dh: keys.p256dh,
                                auth: keys.auth,
                                device_name: navigator.userAgent
                            }),
                        });
                        this.enabled = true;
                        this.status = 'Notifikasi push aktif.';
                    } catch (err) {
                        this.status = 'Gagal mengaktifkan notifikasi: ' + err.message;
                    }
                },
            };
        }
    </script>
</x-app-layout>
