<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

class StartPollSession extends StartSession
{
    protected function storeCurrentUrl(Request $request, $session): void
    {
        // Laravel stores previous URLs in the session. Admin URLs contain secrets.
        if (! $request->is('admin/*', 'adgang/link/*')) {
            parent::storeCurrentUrl($request, $session);
        }
    }
}
