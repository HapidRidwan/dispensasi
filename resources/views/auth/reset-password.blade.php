<x-guest-layout>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.input label="Email" name="email" type="email" :value="old('email', $request->email)" required autofocus :error="$errors->first('email')" />
        <x-ui.input label="Password Baru" name="password" type="password" required autocomplete="new-password" :error="$errors->first('password')" />
        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" required autocomplete="new-password" />

        <x-ui.button type="submit" class="w-full">Reset Password</x-ui.button>
    </form>
</x-guest-layout>
