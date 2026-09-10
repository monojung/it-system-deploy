<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Department;
use App\Models\AuditLog;
use App\Services\TotpService;
use App\Mail\SetupPasswordMail;

class UserController extends Controller
{
    protected TotpService $totpService;

    public function __construct(TotpService $totpService)
    {
        $this->totpService = $totpService;
    }

    public function index(Request $request)
    {
        $query = User::with('department');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by MFA Status
        if ($request->filled('mfa')) {
            if ($request->mfa === 'enabled') {
                $query->where('mfa_enabled', true);
            } elseif ($request->mfa === 'enforced') {
                $query->where('mfa_enforced', true);
            } elseif ($request->mfa === 'disabled') {
                $query->where('mfa_enabled', false)->where('mfa_enforced', false);
            }
        }

        // Filter by Connected Accounts
        if ($request->filled('linked')) {
            if ($request->linked === 'both') {
                $query->whereNotNull('cid')->where('cid', '!=', '')
                      ->where(function ($q) {
                          $q->whereNotNull('google_id')->orWhereNotNull('google_email');
                      });
            } elseif ($request->linked === 'google') {
                $query->where(function ($q) {
                    $q->whereNotNull('google_id')->orWhereNotNull('google_email');
                });
            } elseif ($request->linked === 'thaid') {
                $query->whereNotNull('cid')->where('cid', '!=', '');
            } elseif ($request->linked === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('cid')->orWhere('cid', '');
                })->whereNull('google_id')->whereNull('google_email');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('google_email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('cid', 'like', "%{$cleanSearch}%")
                      ->orWhere('google_id', 'like', "%{$cleanSearch}%");
                }
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Metrics for dashboard headers
        $metrics = [
            'total' => User::count(),
            'google_linked' => User::whereNotNull('google_id')->orWhereNotNull('google_email')->count(),
            'thaid_linked' => User::whereNotNull('cid')->where('cid', '!=', '')->count(),
            'mfa_active' => User::where('mfa_enabled', true)->orWhere('mfa_enforced', true)->count(),
        ];

        return view('users.index', compact('users', 'departments', 'metrics'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $mfaSecret = $this->totpService->generateSecret();
        $otpAuthUri = $this->totpService->getOtpAuthUri('new-user', $mfaSecret, 'Hospital IT');
        $qrCodeUrl = $this->totpService->getQrCodeUrl($otpAuthUri);

        return view('users.create', compact('departments', 'mfaSecret', 'qrCodeUrl'));
    }

    public function store(Request $request)
    {
        $cid = $request->filled('cid') ? preg_replace('/[^0-9]/', '', $request->cid) : null;
        $request->merge(['cid' => $cid]);

        $passwordMode = $request->input('password_mode', 'email_link');

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:it_users,username',
            'cid' => 'nullable|string|size:13|unique:it_users,cid',
            'google_id' => 'nullable|string|max:100|unique:it_users,google_id',
            'google_email' => 'nullable|email|max:255',
            'email' => $passwordMode === 'email_link' ? 'required|email|max:255|unique:it_users,email' : 'nullable|email|max:255|unique:it_users,email',
            'password_mode' => 'required|in:email_link,manual',
            'password' => $passwordMode === 'manual' ? 'required|min:6' : 'nullable',
            'role' => 'required|in:admin,technician,user',
            'department_id' => 'nullable|exists:it_departments,id',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mfa_secret' => 'nullable|string|min:16|max:32',
        ];

        $messages = [
            'name.required' => 'กรุณากรอกชื่อ - นามสกุล',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้งาน (Username)',
            'email.required' => 'กรุณาระบุอีเมล เพื่อให้ระบบสามารถส่งลิงก์ตั้งรหัสผ่านให้ผู้ใช้งานได้',
            'password.required' => 'กรุณาระบุรหัสผ่านเริ่มต้น',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'cid.size' => 'เลขประจำตัวประชาชนต้องมี 13 หลัก',
            'cid.unique' => 'เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว',
            'google_id.unique' => 'Google ID นี้ถูกเชื่อมโยงกับบัญชีอื่นแล้ว',
        ];

        $request->validate($rules, $messages);

        if ($cid && !validate_thai_id($cid)) {
            return back()->withInput()->withErrors(['cid' => 'เลขประจำตัวประชาชน 13 หลักไม่ถูกต้องตามสูตรคำนวณ (Invalid Checksum)']);
        }

        $mfaEnabled = $request->boolean('mfa_enabled');
        $mfaEnforced = $request->boolean('mfa_enforced');
        $mfaSecret = $request->filled('mfa_secret') ? $request->mfa_secret : ($mfaEnabled || $mfaEnforced ? $this->totpService->generateSecret() : null);

        $token = null;
        $expiresAt = null;
        $emailVerifiedAt = now();
        $password = null;

        if ($passwordMode === 'email_link') {
            $token = Str::random(64);
            $expiresAt = now()->addDays(2);
            $emailVerifiedAt = null;
            $password = bcrypt(Str::random(32));
        } else {
            $password = Hash::make($request->password);
            $emailVerifiedAt = now();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'cid' => $cid,
            'google_id' => $request->google_id ?: null,
            'google_email' => $request->google_email ?: null,
            'mfa_enabled' => $mfaEnabled,
            'mfa_enforced' => $mfaEnforced,
            'mfa_secret' => $mfaSecret,
            'mfa_enrolled_at' => ($mfaEnabled || $mfaEnforced) ? now() : null,
            'email' => $request->email,
            'password' => $password,
            'email_verified_at' => $emailVerifiedAt,
            'password_setup_token' => $token,
            'password_setup_expires_at' => $expiresAt,
            'role' => $request->role,
            'department_id' => $request->department_id,
            'position' => $request->position,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $setupUrl = null;
        if ($token) {
            $setupUrl = route('auth.setup-password', ['token' => $token]);
            try {
                Mail::to($user->email)->send(new SetupPasswordMail($user, $setupUrl));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending setup email from admin: ' . $e->getMessage());
            }
        }

        AuditLog::record('create', 'users', "สร้างผู้ใช้งานใหม่ {$user->name} (Role: {$user->role_name}, รหัสผ่าน: " . ($passwordMode === 'email_link' ? 'ส่งลิงก์ทางอีเมล' : 'กำหนดทันที') . ")", $user);

        $flash = [
            'success' => "สร้างบัญชีผู้ใช้งาน {$user->name} สำเร็จ" . ($token ? " (ระบบสร้างลิงก์สำหรับตั้งรหัสผ่านเรียบร้อยแล้ว)" : ""),
        ];

        if ($setupUrl) {
            $flash['setup_link'] = $setupUrl;
            $flash['setup_user_name'] = $user->name;
            $flash['setup_user_email'] = $user->email;
        }

        return redirect()->route('users.index')->with($flash);
    }

    /**
     * Re-send or generate password setup link for a user
     */
    public function sendSetupLink(User $user)
    {
        if (empty($user->email)) {
            return back()->with('error', "ผู้ใช้งาน {$user->name} ยังไม่ได้ระบุอีเมลในระบบ กรุณาแก้ไขข้อมูลและใส่อีเมลก่อนสร้างลิงก์");
        }

        $token = Str::random(64);
        $user->update([
            'password_setup_token' => $token,
            'password_setup_expires_at' => now()->addDays(2),
        ]);

        $setupUrl = route('auth.setup-password', ['token' => $token]);

        try {
            Mail::to($user->email)->send(new SetupPasswordMail($user, $setupUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending setup email: ' . $e->getMessage());
        }

        AuditLog::record('send_setup_link', 'users', "แอดมินสร้างและส่งลิงก์ตั้งรหัสผ่านให้ผู้ใช้ {$user->name} ({$user->email})", $user);

        return redirect()->route('users.index')->with([
            'success' => "สร้างลิงก์ตั้งรหัสผ่านสำหรับ {$user->name} เรียบร้อยแล้ว",
            'setup_link' => $setupUrl,
            'setup_user_name' => $user->name,
            'setup_user_email' => $user->email,
        ]);
    }

    public function edit(User $user)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Ensure user has a valid secret key for Google Authenticator display
        if (empty($user->mfa_secret)) {
            $user->mfa_secret = $this->totpService->generateSecret();
            $user->save();
        }

        $label = $user->username . ($user->email ? " ({$user->email})" : '');
        $otpAuthUri = $this->totpService->getOtpAuthUri($label, $user->mfa_secret, 'Hospital IT');
        $qrCodeUrl = $this->totpService->getQrCodeUrl($otpAuthUri);

        return view('users.edit', compact('user', 'departments', 'qrCodeUrl'));
    }

    public function update(Request $request, User $user)
    {
        $cid = $request->filled('cid') ? preg_replace('/[^0-9]/', '', $request->cid) : null;
        $request->merge(['cid' => $cid]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:it_users,username,' . $user->id,
            'cid' => 'nullable|string|size:13|unique:it_users,cid,' . $user->id,
            'google_id' => 'nullable|string|max:100|unique:it_users,google_id,' . $user->id,
            'google_email' => 'nullable|email|max:255',
            'email' => 'nullable|email|max:255|unique:it_users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,technician,user',
            'department_id' => 'nullable|exists:it_departments,id',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'mfa_secret' => 'nullable|string|min:16|max:32',
        ], [
            'cid.size' => 'เลขประจำตัวประชาชนต้องมี 13 หลัก',
            'cid.unique' => 'เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว',
            'google_id.unique' => 'Google ID นี้ถูกเชื่อมโยงกับบัญชีอื่นแล้ว',
        ]);

        if ($cid && !validate_thai_id($cid)) {
            return back()->withInput()->withErrors(['cid' => 'เลขประจำตัวประชาชน 13 หลักไม่ถูกต้องตามสูตรคำนวณ (Invalid Checksum)']);
        }

        $oldData = $user->only(['name', 'cid', 'google_id', 'google_email', 'mfa_enabled', 'mfa_enforced', 'role', 'is_active']);

        $data = $request->except(['password']);
        $data['cid'] = $cid;
        $data['google_id'] = $request->filled('google_id') ? $request->google_id : null;
        $data['google_email'] = $request->filled('google_email') ? $request->google_email : null;
        $data['mfa_enabled'] = $request->boolean('mfa_enabled');
        $data['mfa_enforced'] = $request->boolean('mfa_enforced');

        if (($data['mfa_enabled'] || $data['mfa_enforced']) && !$user->mfa_enrolled_at) {
            $data['mfa_enrolled_at'] = now();
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        AuditLog::record('update', 'users', "แก้ไขข้อมูลและสิทธิ์ผู้ใช้งาน {$user->name}", $user, $oldData, $user->only(array_keys($oldData)));

        return redirect()->route('users.index')->with('success', "แก้ไขข้อมูลผู้ใช้งาน {$user->name} สำเร็จ");
    }

    /**
     * Quick toggle Google Authenticator MFA for a user
     */
    public function toggleMfa(User $user)
    {
        $newStatus = !$user->mfa_enabled;

        // If turning on and secret is missing, generate one
        $secret = $user->mfa_secret ?: $this->totpService->generateSecret();

        $user->update([
            'mfa_enabled' => $newStatus,
            'mfa_secret' => $secret,
            'mfa_enrolled_at' => $newStatus ? ($user->mfa_enrolled_at ?: now()) : $user->mfa_enrolled_at,
            // If turning off, also unset enforced
            'mfa_enforced' => $newStatus ? $user->mfa_enforced : false,
        ]);

        $statusText = $newStatus ? 'เปิดใช้งาน Google Authenticator (MFA)' : 'ปิดใช้งาน MFA';
        AuditLog::record('toggle_mfa', 'users', "เปลี่ยนสถานะ MFA ของ {$user->name} เป็น {$statusText}", $user);

        return back()->with('success', "เปลี่ยนสถานะความปลอดภัย MFA สำหรับ {$user->name} เป็น: {$statusText}");
    }

    /**
     * Reset Google Authenticator Secret Key for user who lost their device
     */
    public function resetMfa(User $user)
    {
        $newSecret = $this->totpService->generateSecret();
        $user->update([
            'mfa_secret' => $newSecret,
            'mfa_enrolled_at' => now(),
        ]);

        AuditLog::record('reset_mfa', 'users', "รีเซ็ตคีย์ Google Authenticator ให้แก่ผู้ใช้งาน {$user->name}", $user);

        return back()->with('success', "รีเซ็ตคีย์ Google Authenticator สำหรับ {$user->name} เรียบร้อยแล้ว กรุณาให้ผู้ใช้สแกน QR Code ใหม่");
    }

    /**
     * Toggle MFA Enforced policy for user (Enforce vs Self-Service)
     */
    public function toggleEnforceMfa(User $user)
    {
        $newEnforced = !$user->mfa_enforced;
        $secret = $user->mfa_secret ?: $this->totpService->generateSecret();

        $user->update([
            'mfa_enforced' => $newEnforced,
            'mfa_enabled' => $newEnforced ? true : $user->mfa_enabled,
            'mfa_secret' => $secret,
            'mfa_enrolled_at' => $newEnforced ? ($user->mfa_enrolled_at ?: now()) : $user->mfa_enrolled_at,
        ]);

        $statusText = $newEnforced ? 'บังคับใช้ MFA (Admin Enforced - ผู้ใช้ไม่สามารถปิดได้เอง)' : 'ยกเลิกการบังคับใช้ (ผู้ใช้สามารถเปิด/ปิดได้เองใน Profile)';
        AuditLog::record('toggle_enforce_mfa', 'users', "ปรับนโยบายความปลอดภัย MFA ของ {$user->name} เป็น: {$statusText}", $user);

        return back()->with('success', "ปรับนโยบาย MFA สำหรับ {$user->name} เรียบร้อยแล้ว: {$statusText}");
    }

    /**
     * Unlink Google Account for user
     */
    public function unlinkGoogle(User $user)
    {
        $oldEmail = $user->google_email;
        $user->update([
            'google_id' => null,
            'google_email' => null,
        ]);

        AuditLog::record('unlink_google', 'users', "แอดมินยกเลิกการเชื่อมโยง Google ({$oldEmail}) สำหรับผู้ใช้ {$user->name}", $user);

        return back()->with('success', "ตัดการเชื่อมโยงบัญชี Google สำหรับผู้ใช้งาน {$user->name} เรียบร้อยแล้ว");
    }

    /**
     * Unlink Thai ID (CID) for user
     */
    public function unlinkThaid(User $user)
    {
        $oldCid = $user->formatted_cid;
        $user->update([
            'cid' => null,
        ]);

        AuditLog::record('unlink_thaid', 'users', "แอดมินล้างเลขบัตรประชาชน Thai ID ({$oldCid}) สำหรับผู้ใช้ {$user->name}", $user);

        return back()->with('success', "ล้างการเชื่อมโยงบัตรประชาชน Thai ID สำหรับผู้ใช้งาน {$user->name} เรียบร้อยแล้ว");
    }

    /**
     * Return JSON identity details & QR Code for helpdesk modal
     */
    public function getIdentityDetails(User $user)
    {
        if (empty($user->mfa_secret)) {
            $user->mfa_secret = $this->totpService->generateSecret();
            $user->save();
        }

        $label = $user->username . ($user->email ? " ({$user->email})" : '');
        $otpAuthUri = $this->totpService->getOtpAuthUri($label, $user->mfa_secret, 'Hospital IT');
        $qrCodeUrl = $this->totpService->getQrCodeUrl($otpAuthUri);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'role_name' => $user->role_name,
            'role_badge' => $user->role_badge,
            'department' => $user->department?->name ?? 'ไม่ระบุสังกัด',
            'cid' => $user->cid,
            'formatted_cid' => $user->formatted_cid,
            'has_thaid' => $user->hasThaidLinked(),
            'google_email' => $user->google_email,
            'google_id' => $user->google_id,
            'has_google' => $user->hasGoogleLinked(),
            'mfa_enabled' => (bool) $user->mfa_enabled,
            'mfa_enforced' => (bool) $user->mfa_enforced,
            'mfa_secret' => $user->mfa_secret,
            'mfa_enrolled_at' => $user->mfa_enrolled_at ? thai_date($user->mfa_enrolled_at, 'full') : null,
            'qr_code_url' => $qrCodeUrl,
            'otp_auth_uri' => $otpAuthUri,
        ]);
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถระงับการใช้งานบัญชีของตนเองได้');
        }

        $user->update(['is_active' => !$user->is_active]);
        $statusText = $user->is_active ? 'เปิดใช้งาน' : 'ระงับการใช้งาน';

        AuditLog::record('toggle_active', 'users', "ปรับสถานะการใช้งานผู้ใช้ {$user->name} เป็น {$statusText}", $user, ['is_active' => !$user->is_active], ['is_active' => $user->is_active]);

        return back()->with('success', "เปลี่ยนสถานะผู้ใช้ {$user->name} เป็น {$statusText} เรียบร้อยแล้ว");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีผู้ใช้งานของตนเองได้');
        }

        if ($user->repairsRequested()->count() > 0 || $user->repairsAssigned()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบผู้ใช้งานนี้ได้ เนื่องจากมีประวัติการแจ้งซ่อมหรือการปฏิบัติงานช่างในระบบ');
        }

        $name = $user->name;
        $user->delete();

        AuditLog::record('delete', 'users', "ลบบัญชีผู้ใช้งาน {$name}", null);

        return redirect()->route('users.index')->with('success', "ลบบัญชีผู้ใช้งาน {$name} สำเร็จ");
    }
}
