<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a personal access token for API authentication.
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $username = trim($request->input('username'));
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        // Determine token abilities based on role
        $abilities = ['project:read', 'publication:read'];

        $isAdmin = false;
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('The Creator') || $user->hasRole('Admin Research') || $user->hasRole('Admin Project')) {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $abilities = ['*'];
        } elseif ($user->canAccessFullBackend()) {
            $abilities[] = 'project:write';
            $abilities[] = 'publication:write';
        }

        $deviceName = $request->input('device_name', 'api-client');
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'user' => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
                'username' => (string) $user->username,
                'email' => (string) $user->email,
                'researcher_id' => (string) ($user->researcher_id ?? ''),
                'department_id' => (string) ($user->onboarding_department_id ?? $user->researcher?->department_id ?? ''),
            ],
        ]);
    }

    /**
     * Get profile of currently authenticated token user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing(['researcher.department', 'researcher.prefix']);

        return response()->json([
            'user' => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
                'username' => (string) $user->username,
                'email' => (string) $user->email,
                'researcher' => $user->researcher ? [
                    'id' => (string) $user->researcher->researcher_id,
                    'pmis' => (string) ($user->researcher->tea_code ?? ''),
                    'full_name' => trim(($user->researcher->prefix?->prefix_nameTH ?? '') . $user->researcher->researcher_fname . ' ' . $user->researcher->researcher_lname),
                    'department' => (string) ($user->researcher->department?->department_nameTH ?? ''),
                ] : null,
            ],
            'current_token_abilities' => $user->currentAccessToken()->abilities ?? [],
        ]);
    }

    /**
     * Revoke the token currently being used.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ออกจากระบบและเพิกถอน Token สำเร็จ',
        ]);
    }
}
