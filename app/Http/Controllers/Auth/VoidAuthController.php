<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VoidAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $codeVerifier = Str::random(128);
        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
        $state = Str::random(40);

        $request->session()->put('voidauth_code_verifier', $codeVerifier);
        $request->session()->put('voidauth_state', $state);

        $query = http_build_query([
            'client_id' => config('voidauth.client_id'),
            'redirect_uri' => config('voidauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'profile',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect(config('voidauth.url').'/oauth/authorize?'.$query);
    }

    public function callback(Request $request)
    {
        if ($request->state !== $request->session()->pull('voidauth_state')) {
            return redirect('/')->withErrors(['error' => 'Ongeldige state parameter.']);
        }

        if ($request->has('error')) {
            return redirect('/')->withErrors(['error' => $request->error_description ?? 'Authenticatie geweigerd.']);
        }

        $codeVerifier = $request->session()->pull('voidauth_code_verifier');

        // Exchange code for tokens
        try {
            $tokenResponse = Http::timeout(10)->post(config('voidauth.url').'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('voidauth.client_id'),
                'client_secret' => config('voidauth.client_secret'),
                'redirect_uri' => config('voidauth.redirect_uri'),
                'code' => $request->code,
                'code_verifier' => $codeVerifier,
            ]);
        } catch (\Exception $e) {
            return redirect('/')->withErrors(['error' => 'Authenticatieserver niet bereikbaar.']);
        }

        if (! $tokenResponse->successful()) {
            return redirect('/')->withErrors(['error' => 'Token uitwisseling mislukt.']);
        }

        $tokens = $tokenResponse->json();

        // Fetch user info
        try {
            $userResponse = Http::timeout(10)
                ->withToken($tokens['access_token'])
                ->get(config('voidauth.url').'/api/user');
        } catch (\Exception $e) {
            return redirect('/')->withErrors(['error' => 'Gebruikersinformatie ophalen mislukt.']);
        }

        if (! $userResponse->successful()) {
            return redirect('/')->withErrors(['error' => 'Gebruikersinformatie ophalen mislukt.']);
        }

        $voidAuthUser = $userResponse->json();

        // Find or create local user
        $user = User::where('email', $voidAuthUser['email'])->first();

        if ($user) {
            $user->update([
                'name' => $voidAuthUser['name'],
                'voidauth_id' => $voidAuthUser['id'],
            ]);
        } else {
            $user = User::create([
                'name' => $voidAuthUser['name'],
                'email' => $voidAuthUser['email'],
                'password' => bcrypt(Str::random(32)),
                'voidauth_id' => $voidAuthUser['id'],
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        $request->session()->put('voidauth_access_token', $tokens['access_token']);
        $request->session()->put('voidauth_refresh_token', $tokens['refresh_token'] ?? null);

        return redirect()->intended('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirectUrl = urlencode(config('app.url'));

        return redirect(config('voidauth.url').'/login?redirect='.$redirectUrl);
    }
}
