<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kullanıcı giriş yapmışsa VE admin ise geçişe izin ver
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Admin değilse isteği engelle ve hata dön
        abort(403, 'Bu alana erişim yetkiniz bulunmamaktadır.');
    }
}
