<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureCsrfTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip untuk route login
        if ($request->is('login') || $request->is('login/*')) {
            return $next($request);
        }

        // Regenerate CSRF token jika session expired
        if ($request->session()->has('errors') && 
            $request->session()->get('errors')->has('_token')) {
            
            Log::warning('CSRF token expired, regenerating session');
            $request->session()->regenerateToken();
            
            return redirect()->back()
                ->with('error', 'Sesi Anda telah berakhir. Silakan coba lagi.');
        }

        // Pastikan CSRF token tersedia di semua form kecuali login
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
            if (!$request->has('_token') && !$request->header('X-CSRF-TOKEN')) {
                Log::warning('CSRF token missing for request: ' . $request->url());
                return redirect()->back()
                    ->with('error', 'Token keamanan tidak ditemukan. Silakan refresh halaman.');
            }
        }

        return $next($request);
    }
} 