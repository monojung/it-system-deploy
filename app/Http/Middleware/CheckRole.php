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
            if ($user->approval_status === 'pending') {
                return redirect()->route('auth.pending-approval');
            }
            auth()->logout();
            return redirect()->route('login')->with('error', 'บัญชีผู้ใช้งานของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ IT');
        }

        // Super Admin always has full access to all modules and system actions
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        $roleLabels = [
            'super_admin' => 'แอดมินระบบ (Super Admin)',
            'admin' => 'แอดมิน (Admin)',
            'technician' => 'เจ้าหน้าที่ IT',
            'user' => 'ผู้ใช้งาน (User)',
        ];
        $formattedRoles = array_map(fn($r) => $roleLabels[$r] ?? $r, $roles);
        $roleStr = implode(' หรือ ', $formattedRoles);

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, 'คุณไม่มีสิทธิ์ในการเข้าถึง (เฉพาะ ' . $roleStr . ' เท่านั้น)');
        }

        return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์ในการเข้าถึงเมนูนี้ (เฉพาะ ' . $roleStr . ' เท่านั้น)');
    }
}
