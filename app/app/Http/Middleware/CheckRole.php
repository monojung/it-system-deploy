<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'บัญชีผู้ใช้งานของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ IT');
        }

        // Admin always has full access
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์ในการเข้าถึงเมนูนี้ (เฉพาะ ' . implode(', ', $roles) . ' เท่านั้น)');
    }
}
