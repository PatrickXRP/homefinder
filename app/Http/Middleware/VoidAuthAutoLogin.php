<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VoidAuthAutoLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(config('voidauth.url')) || config('voidauth.url') === 'disabled') {
            return $next($request);
        }

        if (! Auth::check()) {
            // Store the intended URL so we can redirect back after login
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect('/auth/redirect');
        }

        return $next($request);
    }
}
