<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class localization {
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Check header request and determine localizaton
       $local = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : config('app.locale');
        // $local = 'en';
        app()->setLocale($local);
        return $next($request);
    }
}
