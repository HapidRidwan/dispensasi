<x-guest-layout>
    <div x-data="{ recovery: false }">
        <div class="mb-5">
            <h2 class="text-base font-semibold text-ink" x-show="! recovery">Verifikasi Dua Faktor</h2>
            <h2 class="text-base font-semibold text-ink" x-cloak x-show="recovery">Gunakan Recovery Code</h2>
        </div>

        <p class="text-sm text-muted mb-5" x-show="! recovery">
            Masukkan kode 6 digit dari aplikasi authenticator (Google Authenticator, Authy, dll.)
            untuk melanjutkan login.
        </p>
        <p class="text-sm text-muted mb-5" x-cloak x-show="recovery">
            Masukkan salah satu recovery code Anda untuk mengakses akun tanpa aplikasi authenticator.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
            @csrf

            {{-- OTP Code input --}}
            <div x-show="! recovery">
                <label for="code" class="block text-sm font-medium text-ink mb-1">Kode OTP</label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    maxlength="6"
                    autofocus
                    x-ref="code"
                    autocomplete="one-time-code"
                    class="block w-full rounded-lg border-line text-sm focus:border-primary focus:ring-primary/30 font-mono tracking-widest text-center"
                    placeholder="000000"
                >
            </div>

            {{-- Recovery Code input --}}
            <div x-cloak x-show="recovery">
                <label for="recovery_code" class="block text-sm font-medium text-ink mb-1">Recovery Code</label>
                <input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    x-ref="recovery_code"
                    autocomplete="one-time-code"
                    class="block w-full rounded-lg border-line text-sm focus:border-primary focus:ring-primary/30 font-mono"
                    placeholder="xxxxxxxx-xxxxxxxx"
                >
            </div>

            <div class="flex items-center justify-between pt-1">
                {{-- Toggle recovery / OTP --}}
                <button
                    type="button"
                    class="text-sm text-primary hover:underline"
                    x-show="! recovery"
                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })"
                >
                    Gunakan recovery code
                </button>
                <button
                    type="button"
                    class="text-sm text-primary hover:underline"
                    x-cloak
                    x-show="recovery"
                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })"
                >
                    Gunakan kode OTP
                </button>

                <x-ui.button type="submit">Verifikasi</x-ui.button>
            </div>
        </form>
    </div>
</x-guest-layout>
