<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $locale = in_array($request->user()?->locale, config('locales.supported'), true) ? $request->user()?->locale : config('locales.default');
        } else {
            $locale = config('locales.default');
        }
        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
