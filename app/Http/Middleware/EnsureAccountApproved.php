<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
{
    /**
     * Handle an incoming request.
     * Ensure that the authenticated user has completed onboarding and is approved by Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Admins always have full bypass access
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 1. If user has not completed onboarding (name + department selection)
        if (!$user->hasCompletedOnboarding()) {
            // Allow access to complete-profile routes and logout
            if ($request->routeIs('auth.complete-profile', 'auth.complete-profile.post', 'logout')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'requires_onboarding' => true,
                    'message' => 'กรุณากรอกข้อมูลชื่อ-สกุล และเลือกแผนกของท่านก่อนเข้าใช้งานระบบ',
                    'redirect' => route('auth.complete-profile'),
                ], 403);
            }

            return redirect()->route('auth.complete-profile')->with('info', 'กรุณาระบุชื่อ-สกุล และเลือกแผนกที่ท่านสังกัด เพื่อส่งข้อมูลให้ผู้ดูแลระบบตรวจสอบยืนยันตัวตน');
        }

        // 2. If user is pending approval or rejected
        if (!$user->isApproved()) {
            // Allow access to pending-approval view, complete-profile, and logout
            if ($request->routeIs('auth.pending-approval', 'auth.complete-profile', 'auth.complete-profile.post', 'logout')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'approval_status' => $user->approval_status,
                    'message' => 'บัญชีผู้ใช้งานของคุณอยู่ระหว่างรอผู้ดูแลระบบตรวจสอบและยืนยันตัวตน',
                    'redirect' => route('auth.pending-approval'),
                ], 403);
            }

            return redirect()->route('auth.pending-approval');
        }

        return $next($request);
    }
}
