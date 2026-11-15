<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsNotBlocked
{
    /**
     * Sign a blocked user out on the first request, whether the session is new (right after
     * signing in, also with two-factor authentication or "remember me") or older than the block.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlocked() !== true) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->withErrors([
            'email' => __('This account has been blocked. Please contact the organiser.'),
        ]);
    }
}
