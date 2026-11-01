<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supported = ['fr', 'en', 'es', 'de'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Check the session first (user explicitly chose a language)
        if (Session::has('locale') && in_array(Session::get('locale'), $this->supported)) {
            App::setLocale(Session::get('locale'));

            return $next($request);
        }

        // 2. Detect browser preferred language
        $browserLang = $this->detectBrowserLocale($request);

        if ($browserLang) {
            App::setLocale($browserLang);
            Session::put('locale', $browserLang);
        }

        return $next($request);
    }

    /**
     * Detect the best matching locale from the Accept-Language header.
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language', '');

        // Parse the Accept-Language header: e.g. "fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7"
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $segments = explode(';q=', trim($part));
            $lang = strtolower(substr(trim($segments[0]), 0, 2));
            $quality = isset($segments[1]) ? (float) $segments[1] : 1.0;
            $languages[$lang] = $quality;
        }

        // Sort by quality descending
        arsort($languages);

        // Return first supported locale
        foreach (array_keys($languages) as $lang) {
            if (in_array($lang, $this->supported)) {
                return $lang;
            }
        }

        return null;
    }
}
