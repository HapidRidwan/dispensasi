<div>
    <div class="space-y-4">

        {{-- Status & instruksi --}}
        @if ($this->enabled)
            @if ($showingConfirmation)
                <p class="text-sm text-ink font-medium">
                    Langkah 2: Konfirmasi Kode OTP
                </p>
                <p class="text-sm text-muted">
                    Scan QR code di bawah menggunakan aplikasi authenticator (Google Authenticator, Authy, dll.),
                    kemudian masukkan kode 6 digit yang dihasilkan untuk mengkonfirmasi aktivasi.
                </p>
            @else
                <p class="text-sm text-ink font-medium">
                    ✓ Two-Factor Authentication telah aktif dan dikonfirmasi.
                </p>
            @endif
        @else
            <p class="text-sm text-muted">
                Klik <strong>Aktifkan 2FA</strong> untuk memulai proses pengaturan. Anda akan diminta
                mengkonfirmasi password lalu men-scan QR code.
            </p>
        @endif

        {{-- QR Code & Setup Key --}}
        @if ($this->enabled && $showingQrCode)
            <div class="p-3 bg-white inline-block border border-line rounded-lg">
                {!! $this->user->twoFactorQrCodeSvg() !!}
            </div>

            <div class="text-sm">
                <span class="font-medium text-ink">Setup Key (manual):</span>
                <code class="ml-2 px-2 py-0.5 bg-background rounded text-xs font-mono text-ink select-all">
                    {{ decrypt($this->user->two_factor_secret) }}
                </code>
            </div>

            @if ($showingConfirmation)
                <div class="space-y-1">
                    <label for="2fa-code" class="block text-sm font-medium text-ink">Kode 6 Digit dari Aplikasi</label>
                    <input
                        id="2fa-code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        autocomplete="one-time-code"
                        wire:model="code"
                        wire:keydown.enter="confirmTwoFactorAuthentication"
                        class="block w-48 rounded-lg border-line text-sm focus:border-primary focus:ring-primary/30 font-mono tracking-widest text-center"
                        placeholder="000000"
                        autofocus
                    >
                    @error('code')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        @endif

        {{-- Recovery Codes --}}
        @if ($this->enabled && $showingRecoveryCodes)
            <div>
                <p class="text-sm font-medium text-ink mb-2">Recovery Codes</p>
                <p class="text-xs text-muted mb-3">
                    Simpan kode ini di tempat yang aman. Gunakan saat tidak bisa mengakses aplikasi authenticator.
                </p>
                <div class="bg-background border border-line rounded-lg p-4 grid grid-cols-2 gap-1 font-mono text-xs text-ink">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div class="py-0.5">{{ $code }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-wrap items-center gap-2 pt-1">
            @if (! $this->enabled)
                {{-- Tombol Aktifkan --}}
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-ui.button type="button" wire:loading.attr="disabled">
                        Aktifkan 2FA
                    </x-ui.button>
                </x-confirms-password>

            @else
                @if ($showingConfirmation)
                    {{-- Konfirmasi kode OTP --}}
                    <x-ui.button type="button" wire:click="confirmTwoFactorAuthentication" wire:loading.attr="disabled">
                        Konfirmasi
                    </x-ui.button>

                    {{-- Batal (nonaktifkan kembali) --}}
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-ui.button type="button" variant="secondary" wire:loading.attr="disabled">
                            Batal
                        </x-ui.button>
                    </x-confirms-password>

                @elseif ($showingRecoveryCodes)
                    {{-- Regenerate recovery codes --}}
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-ui.button type="button" variant="secondary" wire:loading.attr="disabled">
                            Regenerate Recovery Codes
                        </x-ui.button>
                    </x-confirms-password>

                    {{-- Sembunyikan recovery codes --}}
                    <x-ui.button type="button" variant="ghost" wire:click="$set('showingRecoveryCodes', false)">
                        Sembunyikan
                    </x-ui.button>

                @else
                    {{-- Tampilkan Recovery Codes --}}
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-ui.button type="button" variant="secondary" wire:loading.attr="disabled">
                            Lihat Recovery Codes
                        </x-ui.button>
                    </x-confirms-password>
                @endif

                @if (! $showingConfirmation)
                    {{-- Nonaktifkan 2FA --}}
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-ui.button type="button" variant="danger" wire:loading.attr="disabled">
                            Nonaktifkan 2FA
                        </x-ui.button>
                    </x-confirms-password>
                @endif
            @endif
        </div>

    </div>
</div>
