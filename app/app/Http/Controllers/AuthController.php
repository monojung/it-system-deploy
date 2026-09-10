<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Mail\SetupPasswordMail;

class AuthController extends Controller
{
    /**
     * Show modern login page (Email, Google & ThaID)
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $googleClientId = setting('google_client_id') ?: config('services.google.client_id', '');
        $thaidClientId = setting('thaid_client_id', '');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $activeTab = $request->query('tab', session('active_tab', 'login'));

        return view('auth.login', [
            'googleClientId' => $googleClientId,
            'hasGoogleConfig' => !empty($googleClientId),
            'hasThaidConfig' => !empty($thaidClientId),
            'departments' => $departments,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Handle Email/Password Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'กรุณากรอกอีเมลสำหรับเข้าสู่ระบบ',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('email'))->withErrors([
                'email' => "คุณกรอกรหัสผ่านผิดเกินกำหนด กรุณารออีก {$seconds} วินาทีก่อนลองใหม่",
            ]);
        }

        $input = trim($request->input('email'));
        $user = User::where('email', $input)
            ->orWhere('username', $input)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง',
            ]);
        }

        RateLimiter::clear($throttleKey);

        if (!$user->is_active) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อกลุ่มงานสุขภาพดิจิทัล',
            ]);
        }

        // Check if user has not set password / verified email yet
        if (!$user->isEmailVerified() && !empty($user->password_setup_token)) {
            return back()->withInput($request->only('email'))->with('show_resend_link', true)->withErrors([
                'email' => 'บัญชีของคุณยังไม่ได้ยืนยันและตั้งรหัสผ่านผ่านลิงก์อีเมล กรุณาตรวจสอบกล่องข้อความอีเมลของท่าน หรือกดขอรับลิงก์ยืนยันใหม่',
            ]);
        }

        // Check MFA Challenge
        if ($user->isMfaActive()) {
            $request->session()->put('mfa_pending_user_id', $user->id);
            $request->session()->put('mfa_provider', 'email');
            return redirect()->route('auth.mfa-challenge');
        }

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();
        AuditLog::record('login', 'auth', "เข้าสู่ระบบด้วยอีเมล ({$user->email})", $user, null, null, $request, $user);

        return redirect()->intended(route('dashboard'))->with('success', "ยินดีต้อนรับ {$user->name} เข้าสู่ระบบสารสนเทศสำเร็จ");
    }

    /**
     * Show Registration Form (redirect to login with tab=register)
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login', ['tab' => 'register']);
    }

    /**
     * Handle User Registration without Password (Send setup email link)
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:it_users,email',
            'department_id' => 'required|exists:it_departments,id',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
        ], [
            'first_name.required' => 'กรุณากรอกชื่อจริง',
            'last_name.required' => 'กรุณากรอกนามสกุลจริง',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกลงทะเบียนใช้งานในระบบแล้ว กรุณาเข้าสู่ระบบ หรือใช้เมนูลืมรหัสผ่าน',
            'department_id.required' => 'กรุณาเลือกกลุ่มงาน / ฝ่าย / แผนก',
            'department_id.exists' => 'ไม่พบข้อมูลแผนกที่เลือก',
        ]);

        $fullName = trim($request->first_name . ' ' . $request->last_name);
        $email = strtolower(trim($request->email));

        // Generate unique username based on email
        $baseUsername = Str::slug(explode('@', $email)[0], '_') ?: 'staff';
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . rand(100, 999);
            $counter++;
            if ($counter > 10) {
                $username = $baseUsername . '_' . time();
                break;
            }
        }

        $token = Str::random(64);
        $expiresAt = now()->addHours(24);

        $user = User::create([
            'name' => $fullName,
            'username' => $username,
            'email' => $email,
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'position' => $request->position ?: 'บุคลากรโรงพยาบาล',
            'role' => 'user',
            'is_active' => true,
            'password' => bcrypt(Str::random(32)), // Random placeholder until first-time setup
            'email_verified_at' => null,
            'password_setup_token' => $token,
            'password_setup_expires_at' => $expiresAt,
        ]);

        $setupUrl = route('auth.setup-password', ['token' => $token]);

        $this->configureMailDriver();

        try {
            Mail::to($user->email)->send(new SetupPasswordMail($user, $setupUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending setup email: ' . $e->getMessage());
        }

        AuditLog::record('register', 'auth', "ลงทะเบียนผู้ใช้งานใหม่ ({$user->name}, {$user->email}) ส่งลิงก์ยืนยันและตั้งรหัสผ่าน", $user, null, null, $request, $user);

        return redirect()->route('auth.verify-notice')->with([
            'registered_email' => $user->email,
            'registered_name' => $user->name,
            'dev_setup_url' => $setupUrl,
            'success' => 'ลงทะเบียนสำเร็จ ระบบได้ส่งลิงก์ยืนยันและตั้งรหัสผ่านไปยังอีเมลของคุณแล้ว',
        ]);
    }

    /**
     * Configure Mail Driver dynamically from database settings or .env
     */
    private function configureMailDriver(): void
    {
        $mailer = setting('mail_mailer') ?: config('mail.default', 'log');
        $host = setting('mail_host') ?: config('mail.mailers.smtp.host');
        $port = setting('mail_port') ?: config('mail.mailers.smtp.port', 587);
        $username = setting('mail_username') ?: config('mail.mailers.smtp.username');
        $password = setting('mail_password') ?: config('mail.mailers.smtp.password');
        $encryption = setting('mail_encryption') ?: config('mail.mailers.smtp.encryption', 'tls');
        $fromAddress = setting('mail_from_address') ?: config('mail.from.address', $username ?: 'noreply@thchospital.go.th');
        $fromName = setting('mail_from_name') ?: setting('hospital_name_th', config('mail.from.name', 'โรงพยาบาลทุ่งหัวช้าง'));

        if ($mailer === 'smtp' && !empty($host) && !empty($username)) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);
        }
    }

    /**
     * Show Check Email / Verification Notice page
     */
    public function showVerifyNotice(Request $request)
    {
        $email = session('registered_email') ?: $request->query('email', '');
        $name = session('registered_name', '');
        $devSetupUrl = session('dev_setup_url');

        if (!empty($email)) {
            $user = User::where('email', $email)->first();
            if ($user && !empty($user->password_setup_token)) {
                $devSetupUrl = route('auth.setup-password', ['token' => $user->password_setup_token]);
                if (empty($name)) {
                    $name = $user->name;
                }
            }
        }

        $isLogMailer = config('mail.default') === 'log';

        return view('auth.verify_notice', compact('email', 'name', 'devSetupUrl', 'isLogMailer'));
    }

    /**
     * Resend Setup / Verification Link
     */
    public function resendSetupLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'กรุณาระบุอีเมลที่ต้องการรับลิงก์ยืนยัน',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('info', "หากอีเมล {$email} มีอยู่ในระบบ ลิงก์ตั้งรหัสผ่านจะถูกส่งไปยังกล่องข้อความของท่าน");
        }

        if ($user->isEmailVerified() && empty($user->password_setup_token)) {
            return redirect()->route('login')->with('info', 'บัญชีนี้ได้รับการยืนยันและตั้งรหัสผ่านเรียบร้อยแล้ว ท่านสามารถเข้าสู่ระบบ หรือใช้เมนูลืมรหัสผ่านได้ทันที');
        }

        $token = Str::random(64);
        $user->update([
            'password_setup_token' => $token,
            'password_setup_expires_at' => now()->addHours(24),
        ]);

        $setupUrl = route('auth.setup-password', ['token' => $token]);

        $this->configureMailDriver();

        try {
            Mail::to($user->email)->send(new SetupPasswordMail($user, $setupUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error resending setup email: ' . $e->getMessage());
        }

        AuditLog::record('resend_setup_link', 'auth', "ขอส่งลิงก์ตั้งรหัสผ่านใหม่ ({$user->email})", $user);

        return redirect()->route('auth.verify-notice')->with([
            'registered_email' => $user->email,
            'registered_name' => $user->name,
            'dev_setup_url' => $setupUrl,
            'success' => "ส่งลิงก์ยืนยันและตั้งรหัสผ่านไปยัง {$user->email} เรียบร้อยแล้ว",
        ]);
    }

    /**
     * Show First-Time Password Setup Page
     */
    public function showSetupPassword(string $token)
    {
        $user = User::where('password_setup_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'login' => 'ลิงก์สำหรับตั้งรหัสผ่านไม่ถูกต้อง หรือถูกใช้งานไปแล้ว กรุณาเข้าสู่ระบบ หรือขอรับลิงก์ใหม่',
            ]);
        }

        if ($user->password_setup_expires_at && $user->password_setup_expires_at->isPast()) {
            return redirect()->route('auth.verify-notice', ['email' => $user->email])->withErrors([
                'link' => 'ลิงก์ตั้งรหัสผ่านนี้หมดอายุการใช้งานแล้ว (เกิน 24 ชม.) กรุณากดปุ่มส่งลิงก์ใหม่อีกครั้งด้านล่าง',
            ]);
        }

        return view('auth.setup_password', compact('user', 'token'));
    }

    /**
     * Process First-Time Password Setup
     */
    public function setupPassword(Request $request, string $token)
    {
        $user = User::where('password_setup_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'login' => 'ลิงก์ตั้งรหัสผ่านไม่ถูกต้อง หรือถูกใช้งานไปแล้ว',
            ]);
        }

        if ($user->password_setup_expires_at && $user->password_setup_expires_at->isPast()) {
            return redirect()->route('auth.verify-notice', ['email' => $user->email])->withErrors([
                'link' => 'ลิงก์ตั้งรหัสผ่านนี้หมดอายุแล้ว กรุณากดส่งลิงก์ใหม่',
            ]);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'กรุณากำหนดรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'การยืนยันรหัสผ่านไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง',
        ]);

        $user->update([
            'password' => $request->password,
            'email_verified_at' => now(),
            'password_setup_token' => null,
            'password_setup_expires_at' => null,
            'is_active' => true,
        ]);

        AuditLog::record('password_setup', 'auth', "ยืนยันอีเมลและตั้งรหัสผ่านครั้งแรกสำเร็จ ({$user->name})", $user);

        // Auto login
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', "🎉 ยินดีต้อนรับคุณ {$user->name}! ตั้งรหัสผ่านและยืนยันตัวตนสำเร็จ เข้าสู่ระบบสารสนเทศเรียบร้อยแล้ว");
    }

    /**
     * Show Forgot Password Form
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot_password');
    }

    /**
     * Send Password Reset Link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'กรุณาระบุอีเมลที่ใช้ในระบบ',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);
            $user->update([
                'password_setup_token' => $token,
                'password_setup_expires_at' => now()->addHours(24),
            ]);

            $setupUrl = route('auth.setup-password', ['token' => $token]);

            $this->configureMailDriver();

            try {
                Mail::to($user->email)->send(new SetupPasswordMail($user, $setupUrl));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Reset password email error: ' . $e->getMessage());
            }

            AuditLog::record('forgot_password', 'auth', "ขอรีเซ็ตรหัสผ่าน ({$user->email})", $user);

            return redirect()->route('auth.verify-notice')->with([
                'registered_email' => $user->email,
                'registered_name' => $user->name,
                'dev_setup_url' => $setupUrl,
                'success' => "ส่งลิงก์กำหนดรหัสผ่านใหม่ไปยังอีเมล {$user->email} เรียบร้อยแล้ว",
            ]);
        }

        return redirect()->route('auth.verify-notice')->with([
            'registered_email' => $email,
            'info' => "หากอีเมล {$email} มีอยู่ในระบบ ลิงก์ตั้งรหัสผ่านจะถูกจัดส่งให้ทันที",
        ]);
    }

    /**
     * Configure Google Socialite dynamically from database or .env
     */
    private function configureGoogleSocialite(): bool
    {
        $clientId = setting('google_client_id') ?: config('services.google.client_id');
        $clientSecret = setting('google_client_secret') ?: config('services.google.client_secret');
        $customRedirect = setting('google_redirect_uri') ?: env('GOOGLE_REDIRECT_URI');
        $redirect = !empty($customRedirect) ? $customRedirect : url('/auth/google/callback');

        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => $redirect,
        ]);

        return true;
    }

    /**
     * Redirect to Google OAuth 2.0 (Official Socialite)
     */
    public function redirectToGoogle(Request $request)
    {
        if (!$this->configureGoogleSocialite()) {
            return redirect()->route('login')->with('show_google_setup', true)->withErrors([
                'google' => 'ยังไม่ได้ระบุ Google Client ID และ Client Secret ในระบบ กรุณาติดต่อผู้ดูแลระบบ'
            ]);
        }

        try {
            return Socialite::driver('google')
                ->scopes(['openid', 'email', 'profile'])
                ->with(['prompt' => 'select_account'])
                ->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ Google: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle callback from Google OAuth 2.0
     */
    public function handleGoogleCallback(Request $request)
    {
        if (!$this->configureGoogleSocialite()) {
            return redirect()->route('login')->withErrors(['google' => 'ไม่พบการตั้งค่า Google Client ID ในระบบ']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['google' => 'การยืนยันตัวตนด้วย Google ถูกยกเลิก: ' . $request->error]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();
            $name = $googleUser->getName() ?: 'ผู้ใช้งาน Google';
            $avatar = $googleUser->getAvatar();

            // Check if this is an account linking action from logged-in user profile
            if ($request->session()->has('google_linking_user_id')) {
                $linkingUserId = $request->session()->pull('google_linking_user_id');
                $targetUser = User::find($linkingUserId);
                if ($targetUser) {
                    $duplicate = User::where('id', '!=', $targetUser->id)
                        ->where(function ($q) use ($googleId, $email) {
                            $q->where('google_id', $googleId)->orWhere('google_email', $email);
                        })->first();

                    if ($duplicate) {
                        return redirect()->route('profile')->with('error', "บัญชี Google ({$email}) นี้ถูกเชื่อมโยงอยู่กับผู้ใช้อื่น ({$duplicate->name}) ไปแล้ว");
                    }

                    $targetUser->update([
                        'google_id' => $googleId,
                        'google_email' => $email,
                        'avatar' => $targetUser->avatar ?: $avatar,
                    ]);

                    AuditLog::record('link_google', 'auth', "เชื่อมโยงบัญชี Google ({$email}) เข้ากับบัญชี ({$targetUser->name}) สำเร็จ", $targetUser);

                    return redirect()->route('profile')->with('success', "เชื่อมโยงบัญชี Google ({$email}) สำเร็จเรียบร้อยแล้ว");
                }
            }

            // Match user by google_id, google_email, or primary email
            $user = User::where('google_id', $googleId)
                ->orWhere('google_email', $email)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Update google_id, google_email and avatar
                $user->update([
                    'google_id' => $googleId,
                    'google_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                ]);
            } else {
                // Auto create user account with role 'user'
                $defaultDept = Department::first();
                $user = User::create([
                    'name' => $name,
                    'username' => 'g_' . Str::slug(explode('@', $email)[0], '_') . '_' . substr($googleId, -4),
                    'email' => $email,
                    'google_id' => $googleId,
                    'google_email' => $email,
                    'avatar' => $avatar,
                    'password' => bcrypt(Str::random(24)),
                    'role' => 'user',
                    'department_id' => $defaultDept?->id,
                    'is_active' => true,
                ]);
            }

            if (!$user->is_active) {
                return redirect()->route('login')->withErrors(['login' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อกลุ่มงานสุขภาพดิจิทัล']);
            }

            // Check MFA Challenge
            if ($user->isMfaActive()) {
                $request->session()->put('mfa_pending_user_id', $user->id);
                $request->session()->put('mfa_provider', 'google');
                return redirect()->route('auth.mfa-challenge');
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            AuditLog::record('login', 'auth', "เข้าสู่ระบบด้วย Google Account ({$user->email})", $user, null, null, $request, $user);

            return redirect()->intended(route('dashboard'))->with('success', "ยินดีต้อนรับ {$user->name} เข้าสู่ระบบด้วยบัญชี Google สำเร็จ");
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'เกิดข้อผิดพลาดในการรับข้อมูลจาก Google: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle Google Identity Services (GSI) One-Tap / Button Token verification
     */
    public function handleGoogleToken(Request $request)
    {
        $idToken = $request->input('credential');
        if (empty($idToken)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล Credential'], 400);
        }

        try {
            // Verify token with Google API
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'การยืนยัน Google Token ไม่ผ่านการตรวจสอบ'], 401);
            }

            $googleData = $response->json();
            $googleId = $googleData['sub'] ?? null;
            $email = $googleData['email'] ?? null;
            $name = $googleData['name'] ?? 'ผู้ใช้งาน Google';
            $avatar = $googleData['picture'] ?? null;

            if (empty($email)) {
                return response()->json(['success' => false, 'message' => 'ไม่พบอีเมลจาก Google'], 400);
            }

            $user = User::where('google_id', $googleId)
                ->orWhere('google_email', $email)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'google_email' => $email,
                    'avatar' => $user->avatar ?: $avatar,
                ]);
            } else {
                $defaultDept = Department::first();
                $user = User::create([
                    'name' => $name,
                    'username' => 'g_' . Str::slug(explode('@', $email)[0], '_') . '_' . substr($googleId, -4),
                    'email' => $email,
                    'google_id' => $googleId,
                    'google_email' => $email,
                    'avatar' => $avatar,
                    'password' => bcrypt(Str::random(24)),
                    'role' => 'user',
                    'department_id' => $defaultDept?->id,
                    'is_active' => true,
                ]);
            }

            if (!$user->is_active) {
                return response()->json(['success' => false, 'message' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน'], 403);
            }

            if ($user->isMfaActive()) {
                $request->session()->put('mfa_pending_user_id', $user->id);
                $request->session()->put('mfa_provider', 'google');
                return response()->json([
                    'success' => true,
                    'requires_mfa' => true,
                    'redirect' => route('auth.mfa-challenge'),
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            AuditLog::record('login', 'auth', "เข้าสู่ระบบด้วย Google One-Tap/Button ({$user->email})", $user, null, null, $request, $user);

            return response()->json([
                'success' => true,
                'message' => "เข้าสู่ระบบด้วย Google สำเร็จ ยินดีต้อนรับ {$user->name}",
                'redirect' => route('dashboard'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Redirect to ThaID (DOPA) Gateway
     */
    public function redirectToThaID(Request $request)
    {
        $clientId = setting('thaid_client_id');
        $redirectUri = url('/auth/thaid/callback');

        if (empty($clientId)) {
            return redirect()->route('login')->with('info', 'ยังไม่ได้ระบุ ThaID Client ID ในการตั้งค่าระบบ กรุณาติดต่อผู้ดูแลระบบเพื่อเปิดใช้งาน');
        }

        $state = Str::random(40);
        $request->session()->put('thaid_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'pid openid profile',
            'state' => $state,
        ]);

        return redirect('https://imauth.bora.dopa.go.th/oauth/authorize?' . $query);
    }

    /**
     * Handle callback from ThaID
     */
    public function handleThaIDCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['thaid' => 'การยืนยันตัวตนด้วย ThaID ถูกยกเลิก: ' . $request->error]);
        }

        $code = $request->input('code');
        if (empty($code)) {
            return redirect()->route('login')->withErrors(['thaid' => 'ไม่พบรหัสการยืนยันจาก ThaID']);
        }

        $clientId = setting('thaid_client_id');
        $clientSecret = setting('thaid_client_secret');
        $redirectUri = url('/auth/thaid/callback');

        try {
            $tokenResponse = Http::asForm()->post('https://imauth.bora.dopa.go.th/oauth/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                return redirect()->route('login')->withErrors(['thaid' => 'ไม่สามารถเชื่อมต่อ ThaID Token ได้']);
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            $profileResponse = Http::withToken($accessToken)->get('https://imauth.bora.dopa.go.th/oauth/userinfo');
            if (!$profileResponse->successful()) {
                return redirect()->route('login')->withErrors(['thaid' => 'ไม่สามารถดึงข้อมูลจาก ThaID ได้']);
            }

            $thaidUser = $profileResponse->json();
            $pid = $thaidUser['pid'] ?? null; // 13-digit Thai National ID
            $name = ($thaidUser['th_fname'] ?? '') . ' ' . ($thaidUser['th_lname'] ?? '');

            if (empty($pid)) {
                return redirect()->route('login')->withErrors(['thaid' => 'ไม่พบเลขประจำตัวประชาชนจาก ThaID']);
            }

            $user = User::where('cid', $pid)->first();

            if (!$user) {
                $defaultDept = Department::first();
                $user = User::create([
                    'name' => trim($name) ?: 'บุคลากร ThaID',
                    'username' => 'thaid_' . substr($pid, -6),
                    'cid' => $pid,
                    'password' => bcrypt(Str::random(24)),
                    'role' => 'user',
                    'department_id' => $defaultDept?->id,
                    'is_active' => true,
                ]);
            }

            if (!$user->is_active) {
                return redirect()->route('login')->withErrors(['login' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน กรุณาติดต่อกลุ่มงานสุขภาพดิจิทัล']);
            }

            if ($user->isMfaActive()) {
                $request->session()->put('mfa_pending_user_id', $user->id);
                $request->session()->put('mfa_provider', 'thaid');
                return redirect()->route('auth.mfa-challenge');
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            AuditLog::record('login', 'auth', "เข้าสู่ระบบด้วยบัตรประชาชนดิจิทัล ThaID (CID: {$user->formatted_cid})", $user, null, null, $request, $user);

            return redirect()->intended(route('dashboard'))->with('success', "ยืนยันตัวตนด้วย ThaID (บัตรประชาชนดิจิทัล) สำเร็จ ยินดีต้อนรับ {$user->name}");
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['thaid' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ ThaID: ' . $e->getMessage()]);
        }
    }

    /**
     * Show MFA Challenge (Google Authenticator 6-digit TOTP)
     */
    public function showMfaChallenge(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $userId = $request->session()->get('mfa_pending_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('info', 'กรุณาเข้าสู่ระบบก่อน');
        }

        $user = User::find($userId);
        if (!$user || !$user->is_active) {
            $request->session()->forget('mfa_pending_user_id');
            return redirect()->route('login')->withErrors(['login' => 'ไม่พบบัญชีผู้ใช้ หรือบัญชีถูกระงับการใช้งาน']);
        }

        $provider = $request->session()->get('mfa_provider', 'google');

        return view('auth.mfa_challenge', compact('user', 'provider'));
    }

    /**
     * Verify Google Authenticator 6-digit TOTP code
     */
    public function verifyMfaChallenge(Request $request)
    {
        $userId = $request->session()->get('mfa_pending_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
        }

        $user = User::findOrFail($userId);

        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'กรุณากรอกรหัส 6 หลักจาก Google Authenticator',
        ]);

        $code = preg_replace('/[^0-9]/', '', $request->code);
        $totpService = app(\App\Services\TotpService::class);

        if (!$user->mfa_secret || !$totpService->verifyOtp($user->mfa_secret, $code)) {
            return back()->withErrors(['code' => 'รหัสยืนยันตัวตน 6 หลักไม่ถูกต้อง หรือหมดอายุแล้ว กรุณาตรวจสอบเวลาในโทรศัพท์หรือแอป Google Authenticator']);
        }

        $request->session()->forget(['mfa_pending_user_id', 'mfa_provider']);

        Auth::login($user, true);
        $request->session()->regenerate();

        AuditLog::record('mfa_verify', 'auth', "ยืนยันรหัสความปลอดภัย 2FA ด้วย Google Authenticator สำเร็จ ({$user->name})", $user, null, null, $request, $user);

        return redirect()->intended(route('dashboard'))->with('success', "ยืนยันตัวตนสองชั้น (Google Authenticator MFA) สำเร็จ ยินดีต้อนรับ {$user->name}");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::record('logout', 'auth', "ออกจากระบบ ({$user->name})", $user, null, null, $request, $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('info', 'คุณได้ออกจากระบบเรียบร้อยแล้ว');
    }

    public function profile()
    {
        $user = Auth::user();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $totpService = app(\App\Services\TotpService::class);
        if (empty($user->mfa_secret)) {
            $user->mfa_secret = $totpService->generateSecret();
            $user->save();
        }

        $label = $user->username . ($user->email ? " ({$user->email})" : '');
        $otpAuthUri = $totpService->getOtpAuthUri($label, $user->mfa_secret, 'Hospital IT');
        $qrCodeUrl = $totpService->getQrCodeUrl($otpAuthUri);

        return view('auth.profile', compact('user', 'departments', 'qrCodeUrl'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:it_users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:it_departments,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'position']);
        
        if ($request->has('department_id')) {
            $data['department_id'] = $request->department_id;
        }

        if ($request->filled('cid')) {
            $cid = preg_replace('/[^0-9]/', '', $request->cid);
            if (strlen($cid) !== 13 || !validate_thai_id($cid)) {
                return back()->withInput()->withErrors(['cid' => 'เลขประจำตัวประชาชน 13 หลักไม่ถูกต้องตามสูตรคำนวณ (Invalid Checksum Modulo 11)']);
            }
            $existingCid = User::where('id', '!=', $user->id)->where('cid', $cid)->first();
            if ($existingCid) {
                return back()->withInput()->withErrors(['cid' => 'เลขประจำตัวประชาชนนี้ถูกใช้งานโดยผู้ใช้อื่นในระบบแล้ว']);
            }
            $data['cid'] = $cid;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $filename);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        }

        $user->update($data);

        return back()->with('success', 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);
        AuditLog::record('update', 'auth', "เปลี่ยนรหัสผ่านผู้ใช้งาน ({$user->name})", $user);

        return back()->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }

    /**
     * Enable Google Authenticator (MFA) by verifying 6-digit OTP code
     */
    public function enableMfa(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'otp_code' => 'required|string',
        ], [
            'otp_code.required' => 'กรุณากรอกรหัส 6 หลักจากแอป Google Authenticator',
        ]);

        $code = preg_replace('/[^0-9]/', '', $request->otp_code);
        $totpService = app(\App\Services\TotpService::class);

        if (empty($user->mfa_secret)) {
            $user->mfa_secret = $totpService->generateSecret();
            $user->save();
        }

        if (!$totpService->verifyOtp($user->mfa_secret, $code)) {
            return back()->withErrors(['otp_code' => 'รหัสยืนยัน 6 หลักไม่ถูกต้องหรือหมดอายุแล้ว กรุณาตรวจสอบเวลาในโทรศัพท์และลองใหม่อีกครั้ง']);
        }

        $user->update([
            'mfa_enabled' => true,
            'mfa_enrolled_at' => now(),
        ]);

        AuditLog::record('mfa_enable', 'auth', "ผู้ใช้งาน {$user->name} เปิดใช้งาน Google Authenticator (MFA) ด้วยตนเองสำเร็จ", $user);

        return back()->with('success', 'เปิดใช้งาน Google Authenticator (MFA) สำเร็จเรียบร้อยแล้ว บัญชีของคุณได้รับการคุ้มครองความปลอดภัย 2 ชั้น');
    }

    /**
     * Disable Google Authenticator (MFA)
     */
    public function disableMfa(Request $request)
    {
        $user = Auth::user();

        if ($user->mfa_enforced) {
            return back()->with('error', 'ไม่สามารถปิดการใช้งานได้ เนื่องจากนโยบายความปลอดภัยของระบบกำหนดให้บังคับใช้ MFA โดยผู้ดูแลระบบ (Admin Enforced)');
        }

        // Require password confirmation if user has password
        if (!empty($user->password)) {
            $request->validate([
                'confirm_password' => 'required',
            ], [
                'confirm_password.required' => 'กรุณากรอกรหัสผ่านเข้าสู่ระบบเพื่อยืนยันการปิดใช้งาน Google Authenticator',
            ]);

            if (!\Illuminate\Support\Facades\Hash::check($request->confirm_password, $user->password)) {
                return back()->withErrors(['confirm_password' => 'รหัสผ่านยืนยันไม่ถูกต้อง']);
            }
        }

        $user->update([
            'mfa_enabled' => false,
        ]);

        AuditLog::record('mfa_disable', 'auth', "ผู้ใช้งาน {$user->name} ปิดใช้งาน Google Authenticator (MFA) ด้วยตนเอง", $user);

        return back()->with('success', 'ปิดใช้งาน Google Authenticator เรียบร้อยแล้ว');
    }

    /**
     * Reset Google Authenticator Secret Key
     */
    public function resetMfaSecret(Request $request)
    {
        $user = Auth::user();
        $totpService = app(\App\Services\TotpService::class);
        $newSecret = $totpService->generateSecret();

        $user->update([
            'mfa_secret' => $newSecret,
            'mfa_enabled' => false, // Requires new verification before becoming active
            'mfa_enrolled_at' => null,
        ]);

        AuditLog::record('mfa_reset_secret', 'auth', "ผู้ใช้งาน {$user->name} รีเซ็ตคีย์ Google Authenticator ใหม่", $user);

        return back()->with('success', 'สร้างคีย์ลับและ QR Code ชุดใหม่เรียบร้อยแล้ว กรุณาสแกนเข้าแอป Google Authenticator และกรอกรหัส 6 หลักเพื่อเปิดใช้งาน');
    }

    /**
     * Redirect to Google OAuth for account linking
     */
    public function redirectToGoogleLink(Request $request)
    {
        if (!$this->configureGoogleSocialite()) {
            return redirect()->route('profile')->with('error', 'ระบบยังไม่ได้ระบุ Google Client ID / Secret ในการตั้งค่าระบบ กรุณาติดต่อผู้ดูแลระบบ');
        }

        $request->session()->put('google_linking_user_id', Auth::id());

        try {
            return Socialite::driver('google')
                ->scopes(['openid', 'email', 'profile'])
                ->with(['prompt' => 'select_account'])
                ->redirect();
        } catch (\Exception $e) {
            return redirect()->route('profile')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ Google: ' . $e->getMessage());
        }
    }

    /**
     * Unlink Google account
     */
    public function unlinkGoogle(Request $request)
    {
        $user = Auth::user();

        if (empty($user->password) && empty($user->cid)) {
            return back()->with('error', 'ไม่สามารถยกเลิกการเชื่อมต่อ Google ได้ เนื่องจากไม่มีรหัสผ่านหรือเลขบัตรประชาชนสำหรับเข้าสู่ระบบ กรุณาตั้งรหัสผ่านก่อน');
        }

        $oldEmail = $user->google_email;
        $user->update([
            'google_id' => null,
            'google_email' => null,
        ]);

        AuditLog::record('unlink_google', 'auth', "ยกเลิกการเชื่อมโยงบัญชี Google ({$oldEmail}) จากผู้ใช้งาน {$user->name}", $user);

        return back()->with('success', 'ยกเลิกการเชื่อมโยงบัญชี Google เรียบร้อยแล้ว');
    }

    /**
     * Link or update Thai ID (CID)
     */
    public function linkCid(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'cid' => 'required|string|size:13|unique:it_users,cid,' . $user->id,
        ], [
            'cid.required' => 'กรุณากรอกเลขประจำตัวประชาชน 13 หลัก',
            'cid.size' => 'เลขประจำตัวประชาชนต้องมี 13 หลักพอดี',
            'cid.unique' => 'เลขประจำตัวประชาชนนี้ถูกใช้งานโดยผู้ใช้อื่นในระบบแล้ว',
        ]);

        $cid = preg_replace('/[^0-9]/', '', $request->cid);

        if (!validate_thai_id($cid)) {
            return back()->withErrors(['cid' => 'เลขประจำตัวประชาชน 13 หลักไม่ถูกต้องตามสูตรคำนวณ (Invalid Checksum Modulo 11)']);
        }

        $user->update([
            'cid' => $cid,
        ]);

        AuditLog::record('link_cid', 'auth', "เชื่อมโยงเลขประจำตัวประชาชน Thai ID ({$user->formatted_cid}) ให้กับผู้ใช้งาน {$user->name}", $user);

        return back()->with('success', "เชื่อมโยงเลขประจำตัวประชาชน Thai ID ({$user->formatted_cid}) สำเร็จแล้ว");
    }
}
