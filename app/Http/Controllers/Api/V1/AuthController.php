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
     * Token issuance via API endpoint is strictly disabled.
     * Tokens must be issued exclusively by Super Admin via the Backend Portal.
     */
    public function token(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'ไม่อนุญาตให้ออก Bearer Token ผ่าน API โดยตรง การขอรับ Token ใหม่ต้องให้ผู้ดูแลระบบระดับสูงสุด (Super Admin) สร้างให้ผ่านศูนย์บริหารจัดการ API หลังบ้านเท่านั้น',
            'error' => 'TokenIssuanceRestrictedToSuperAdmin',
        ], 403);
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
