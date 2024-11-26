<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Retrieve the user's preferred locale from the database
            $locale = $request->user()->locale; // Assuming the 'locale' column exists in the users table


        }else{
            // Check the Accept-Language header for a locale or use the default fallback
            $locale = $request->header('Accept-Language', config('app.fallback_locale'));
        }
            // Validate the locale and set it in the application
            if (in_array($locale, ['en', 'ar'])) { // Adjust allowed locales as needed
                App::setLocale($locale);
            }


        return $next($request);
    }
}
