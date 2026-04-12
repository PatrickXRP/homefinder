<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        $redirectUrl = urlencode(config('app.url'));

        return redirect(config('voidauth.url').'/login?redirect='.$redirectUrl);
    }
}
