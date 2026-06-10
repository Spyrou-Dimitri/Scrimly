<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, config('locales.supported'), true)) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back();
    }
}
