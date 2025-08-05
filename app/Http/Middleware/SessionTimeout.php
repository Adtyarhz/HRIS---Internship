<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
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
        // Skip untuk route login dan guest routes
        if ($request->is('login') || $request->is('login/*') || !Auth::check()) {
            return $next($request);
        }

        // Cek apakah user sudah login
        if (Auth::check()) {
            $user = Auth::user();
            $lastActivity = $request->session()->get('last_activity');
            $timeout = config('session.lifetime', 120) * 60; // Convert to seconds
            
            // Jika session sudah expired
            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Log::info('Session expired for user: ' . $user->id);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }
            
            // Update last activity
            $request->session()->put('last_activity', time());
        }
        
        return $next($request);
    }
} 