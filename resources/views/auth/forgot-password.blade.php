<x-guest-layout>
    <p class="mb-5 text-sm text-muted">Masukkan email pemulihan untuk menerima tautan reset password. Jika akun tidak memiliki email pemulihan, hubungi administrator sekolah.</p>

    @session('status')
        <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
    @endsession

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-ui.input label="Email Pemulihan" name="email" type="email" required autofocus :error="$errors->first('email')" />
        <x-ui.button type="submit" class="w-full">Kirim Tautan</x-ui.button>
    </form>
</x-guest-layout>
