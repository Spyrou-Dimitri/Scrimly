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
        $supported = config('locales.supported');
        $default = config('locales.default');

        if ($request->user()) {
            $locale = in_array($request->user()->locale, $supported, true) ? $request->user()->locale : $default;
        } else {
            $sessionLocale = $request->session()->get('locale');
            $locale = in_array($sessionLocale, $supported, true) ? $sessionLocale : $default;
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
