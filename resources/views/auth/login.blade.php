<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        @session('status')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession

        <x-ui.input label="Username" name="username" :value="old('username')" required autofocus autocomplete="username" :error="$errors->first('username')" class="rounded-xl border-slate-200 bg-slate-50/70 py-3 focus:border-primary focus:bg-white focus:ring-primary/20" />
        <x-ui.input label="Password" name="password" type="password" toggleable required autocomplete="current-password" :error="$errors->first('password')" class="rounded-xl border-slate-200 bg-slate-50/70 py-3 focus:border-primary focus:bg-white focus:ring-primary/20" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" class="rounded border-line text-primary focus:ring-primary/30" @checked(old('remember'))>
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-primary hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <x-ui.button type="submit" class="w-full rounded-xl py-3 shadow-lg shadow-primary/20 transition hover:-translate-y-0.5">Masuk</x-ui.button>
    </form>
</x-guest-layout>
