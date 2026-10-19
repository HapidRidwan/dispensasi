<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'admin' && ! $user->two_factor_confirmed_at) {
            return redirect()->route('admin.profile')
                ->with('warning', 'Akun Admin/TU wajib mengaktifkan Two Factor Authentication untuk melanjutkan.');
        }

        return $next($request);
    }
}
