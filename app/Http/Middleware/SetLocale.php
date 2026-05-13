<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\Translation\LanguageSwitcher;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $switcher = new LanguageSwitcher();
        
        // Get locale from request with priority: URL parameter > Session > Cookie > Default
        $locale = $switcher->getLocaleFromRequest($request);
        
        // Set application locale
        App::setLocale($locale);

        return $next($request);
    }
}
