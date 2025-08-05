<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RegenerateSessionToken
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

        // Regenerate session token setiap 30 menit untuk keamanan
        $lastRegeneration = $request->session()->get('token_regenerated_at');
        $regenerationInterval = 30 * 60; // 30 menit dalam detik
        
        if (!$lastRegeneration || (time() - $lastRegeneration) > $regenerationInterval) {
            $request->session()->regenerateToken();
            $request->session()->put('token_regenerated_at', time());
            
            Log::info('Session token regenerated for security');
        }
        
        return $next($request);
    }
} 