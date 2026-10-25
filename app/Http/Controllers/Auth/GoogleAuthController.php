<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SocialAccount;
use App\Models\SystemSetting;
use App\Models\RdbResearcher;
use App\Models\AuthAssignment;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth Consent Screen
     */
    public function redirect()
    {
        // 1. Verify if Google Login is enabled
        $enabled = SystemSetting::get('google_login_enabled', '1');
        if ($enabled !== '1') {
            return redirect()->route('login')->with('error', 'ระบบระงับการเข้าสู่ระบบด้วย Google ชั่วคราว กรุณาใช้ชื่อผู้ใช้และรหัสผ่าน');
        }

        // 2. Fetch Client ID
        $clientId = SystemSetting::get('google_client_id') ?: config('services.google.client_id', env('GOOGLE_CLIENT_ID', ''));
        if (empty($clientId)) {
            return redirect()->route('login')->with('warning', 'ยังไม่ได้กำหนดค่า Google OAuth Client ID ในระบบผู้ดูแลระบบ (กรุณาติดต่อผู้ดูแลระบบ หรือเข้าสู่ระบบด้วยชื่อผู้ใช้และรหัสผ่าน)');
        }

        // 3. Prepare OAuth state and redirect URL
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $redirectUri = route('auth.google.callback');
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ];

        // Domain restriction hint if configured
        $allowedDomains = SystemSetting::get('google_allowed_domains', 'pcru.ac.th');
        if (!empty($allowedDomains) && !str_contains($allowedDomains, ',')) {
            $params['hd'] = trim($allowedDomains);
        }

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth Callback from Google
     */
    public function callback(Request $request)
    {
        // 1. Check for errors from Google
        if ($request->filled('error')) {
            $errorMsg = $request->get('error') === 'access_denied' 
                ? 'การเข้าสู่ระบบด้วยบัญชี Google ถูกยกเลิกโดยผู้ใช้' 
                : 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ Google: ' . $request->get('error');
            return redirect()->route('login')->with('error', $errorMsg);
        }

        $code = $request->get('code');
        $state = $request->get('state');

        if (empty($code) || $state !== session('google_oauth_state')) {
            return redirect()->route('login')->with('error', 'คำขอเข้าสู่ระบบไม่ถูกต้องหรือหมดอายุ (CSRF Validation Failed) กรุณาลองใหม่อีกครั้ง');
        }

        // 2. Exchange authorization code for access token
        $clientId = SystemSetting::get('google_client_id') ?: config('services.google.client_id', env('GOOGLE_CLIENT_ID', ''));
        $clientSecret = SystemSetting::get('google_client_secret') ?: config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', ''));
        $redirectUri = route('auth.google.callback');

        try {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                \Log::error('Google OAuth Token Exchange Failed', ['body' => $tokenResponse->body()]);
                return redirect()->route('login')->with('error', 'ไม่สามารถเชื่อมต่อเพื่อรับโทเค็นจาก Google ได้ กรุณาตรวจสอบการตั้งค่า Client Secret');
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return redirect()->route('login')->with('error', 'ไม่พบ Access Token จากการตอบกลับของ Google');
            }

            // 3. Get User Profile from Google
            $userInfoResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if (!$userInfoResponse->successful()) {
                return redirect()->route('login')->with('error', 'ไม่สามารถดึงข้อมูลโปรไฟล์ผู้ใช้งานจาก Google ได้');
            }

            $googleUser = $userInfoResponse->json();
            $googleId = $googleUser['sub'] ?? null;
            $email = $googleUser['email'] ?? null;
            $name = $googleUser['name'] ?? ($email ? explode('@', $email)[0] : 'Google User');

            if (empty($googleId) || empty($email)) {
                return redirect()->route('login')->with('error', 'ข้อมูลบัญชี Google ไม่สมบูรณ์ (ไม่พบอีเมลหรือรหัสระบุตัวตน)');
            }

            // 4. Validate Domain Policy
            $allowedDomainsSetting = trim(SystemSetting::get('google_allowed_domains', 'pcru.ac.th'));
            if (!empty($allowedDomainsSetting)) {
                $allowedList = array_map('trim', explode(',', strtolower($allowedDomainsSetting)));
                $emailDomain = strtolower(substr(strrchr($email, "@"), 1));
                if (!in_array($emailDomain, $allowedList)) {
                    return redirect()->route('login')->with('error', "ขออภัย อนุญาตเฉพาะอีเมลหน่วยงาน [{$allowedDomainsSetting}] เท่านั้น อีเมล {$email} ไม่ได้รับอนุญาต");
                }
            }

            // 5. Find or Create User
            $user = null;

            // Search by social account record
            $social = SocialAccount::where('provider', 'google')->where('client_id', $googleId)->first();
            if ($social && $social->user_id) {
                $user = User::find($social->user_id);
            }

            // Fallback: search by verified email in user table
            if (!$user) {
                $user = User::where('email', $email)->first();
                if ($user && !$social) {
                    // Link existing user to Google social account
                    SocialAccount::create([
                        'user_id' => $user->id,
                        'provider' => 'google',
                        'client_id' => $googleId,
                        'email' => $email,
                        'username' => $user->username,
                        'data' => json_encode($googleUser),
                        'created_at' => time(),
                    ]);
                }
            }

            // If user still not found, check auto-registration policy
            if (!$user) {
                $autoRegister = SystemSetting::get('google_auto_register', '1');
                if ($autoRegister !== '1') {
                    return redirect()->route('login')->with('error', "ไม่พบบัญชีผู้ใช้งานที่ผูกกับอีเมล {$email} ในระบบ และระบบไม่อนุญาตให้ลงทะเบียนอัตโนมัติ กรุณาติดต่อผู้ดูแลระบบ");
                }

                // Generate unique username
                $baseUsername = explode('@', $email)[0];
                $cleanUsername = preg_replace('/[^a-zA-Z0-9_\.]/', '', $baseUsername);
                if (empty($cleanUsername)) {
                    $cleanUsername = 'user_' . substr($googleId, 0, 6);
                }

                $username = $cleanUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $cleanUsername . $counter++;
                }

                // Check if matching researcher exists by email
                $researcher = RdbResearcher::where('researcher_email', $email)->first();

                // Create User
                $user = User::create([
                    'username' => $username,
                    'email' => $email,
                    'name' => $name,
                    'researcher_id' => $researcher ? $researcher->researcher_id : null,
                    'onboarding_status' => $researcher ? 'approved' : 'none',
                    'password' => Str::random(32),
                    'status' => 10, // Active
                    'confirmed_at' => time(),
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);

                // Assign Researcher role if matching researcher was found
                if ($researcher && class_exists(AuthAssignment::class)) {
                    AuthAssignment::create([
                        'item_name' => 'Researcher',
                        'user_id' => (string)$user->id,
                        'created_at' => time(),
                    ]);
                }

                // Create social account record
                SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => 'google',
                    'client_id' => $googleId,
                    'email' => $email,
                    'username' => $user->username,
                    'data' => json_encode($googleUser),
                    'created_at' => time(),
                ]);
            }

            // If existing user doesn't have researcher linked yet, attempt to link if email now exists in database
            if (!$user->researcher_id) {
                $matched = RdbResearcher::where('researcher_email', $user->email)->first();
                if ($matched) {
                    $user->researcher_id = $matched->researcher_id;
                    $user->onboarding_status = 'approved';
                    $user->save();

                    if (class_exists(AuthAssignment::class) && !AuthAssignment::where('user_id', (string)$user->id)->where('item_name', 'Researcher')->exists()) {
                        AuthAssignment::create([
                            'item_name' => 'Researcher',
                            'user_id' => (string)$user->id,
                            'created_at' => time(),
                        ]);
                    }
                }
            }

            // 6. Check user active status
            if ($user->status != 10) {
                return redirect()->route('login')->with('error', 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
            }

            // 7. Login User
            Auth::login($user, true);
            $request->session()->regenerate();

            if (!$user->canAccessFullBackend()) {
                return redirect()->route('site.index')
                    ->with('status', "เข้าสู่ระบบสำเร็จ ยินดีต้อนรับคุณ {$user->username} ({$email}) เนื่องจากยังไม่มีข้อมูลนักวิจัยในระบบ โปรดทำการยืนยันตัวตนเพื่อขอเปิดใช้งานระบบหลังบ้าน");
            }

            return redirect()->intended(route('backend.site.index'))
                ->with('status', "เข้าสู่ระบบสำเร็จ ยินดีต้อนรับคุณ {$user->username} ({$email})");

        } catch (\Exception $e) {
            \Log::error('Google OAuth Exception', ['message' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'เกิดข้อผิดพลาดในการประมวลผลการเข้าสู่ระบบ: ' . $e->getMessage());
        }
    }
}
