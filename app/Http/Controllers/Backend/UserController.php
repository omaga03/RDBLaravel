<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreUserRequest;
use App\Http\Requests\Backend\UpdateUserRequest;
use App\Models\User;
use App\Models\AuthItem;
use App\Models\AuthAssignment;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'researcher.department', 'researcher.prefix', 'onboardingDepartment', 'onboardingPrefix']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('onboarding_fname', 'like', "%{$search}%")
                  ->orWhere('onboarding_lname', 'like', "%{$search}%")
                  ->orWhereHas('researcher', function ($rq) use ($search) {
                      $rq->where('researcher_fname', 'like', "%{$search}%")
                         ->orWhere('researcher_lname', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $role = $request->role;
            if ($role === 'admin_all' || $role === 'admin_group') {
                $query->whereHas('roles', function ($q) {
                    $q->where('item_name', 'like', 'Admin%')
                      ->orWhere('item_name', 'The Creator');
                });
            } else {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('item_name', $role);
                });
            }
        }

        if ($request->filled('onboarding_status')) {
            $query->where('onboarding_status', $request->onboarding_status);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $roles = AuthItem::where('type', 1)->orderBy('name')->get();
        $pendingCount = User::where('onboarding_status', 'pending')->count();
        $adminCount = User::whereHas('roles', function ($q) {
            $q->where('item_name', 'like', 'Admin%')
              ->orWhere('item_name', 'The Creator');
        })->count();
        $researchers = RdbResearcher::with(['prefix', 'department'])->orderBy('researcher_fname')->get();

        return view('backend.user.index', compact('users', 'roles', 'pendingCount', 'adminCount', 'researchers'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = AuthItem::where('type', 1)->orderBy('name')->get();
        $researchers = RdbResearcher::with(['prefix', 'department'])->orderBy('researcher_fname')->get();

        return view('backend.user.create', compact('roles', 'researchers'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = new User();
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->password_hash = Hash::make($validated['password']);
        $user->auth_key = Str::random(32);
        $user->confirmed_at = time();
        $user->status = $validated['status'];
        $user->researcher_id = $validated['researcher_id'] ?? null;
        $user->user_created = auth()->id();
        $user->save();

        // Assign Role
        AuthAssignment::create([
            'item_name' => $validated['role'],
            'user_id' => $user->id,
            'created_at' => time(),
        ]);

        return redirect()->route('backend.user.index')
            ->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load(['roles', 'researcher']);

        return view('backend.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        $canManageRoles = $currentUser && ($currentUser->hasRole('The Creator') || $currentUser->hasRole('Admin') || $currentUser->hasPermission('User Manager'));

        $roles = $canManageRoles ? AuthItem::where('type', 1)->orderBy('name')->get() : collect();
        $researchers = $canManageRoles ? RdbResearcher::with(['prefix', 'department'])->orderBy('researcher_fname')->get() : collect();
        $userRole = $user->roles()->first()?->item_name ?? ($user->researcher_id ? 'Researcher' : 'General User');

        return view('backend.user.edit', compact('user', 'roles', 'researchers', 'userRole', 'canManageRoles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        $canManageRoles = $currentUser && ($currentUser->hasRole('The Creator') || $currentUser->hasRole('Admin') || $currentUser->hasPermission('User Manager'));

        $validated = $request->validated();

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        if ($canManageRoles) {
            $user->status = $validated['status'];
            $user->researcher_id = $validated['researcher_id'] ?? null;
            if (isset($validated['onboarding_status'])) {
                $user->onboarding_status = $validated['onboarding_status'];
            }
        }

        $user->user_updated = auth()->id();
        $user->save();

        // Sync Role only if user has management permissions
        if ($canManageRoles && isset($validated['role'])) {
            AuthAssignment::where('user_id', $user->id)->delete();
            AuthAssignment::create([
                'item_name' => $validated['role'],
                'user_id' => $user->id,
                'created_at' => time(),
            ]);
        }

        if ($canManageRoles) {
            return redirect()->route('backend.user.index')
                ->with('success', 'ปรับปรุงข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
        }

        return redirect()->back()
            ->with('success', 'ปรับปรุงข้อมูลส่วนตัวเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('backend.user.index')
                ->with('error', 'ไม่สามารถลบบัญชีผู้ใช้ที่กำลังเข้าสู่ระบบอยู่ได้');
        }

        if ($user->hasRole('The Creator') || $user->id === 129) {
            return redirect()->route('backend.user.index')
                ->with('error', 'ไม่สามารถลบบัญชีผู้ดูแลระบบหลัก (The Creator) ได้');
        }

        AuthAssignment::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('backend.user.index')
            ->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }

    /**
     * Login As (Impersonate) a specific user for testing/troubleshooting.
     */
    public function impersonate(Request $request, User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('The Creator') && $currentUser->id !== 129 && !session()->has('impersonator_id')) {
            abort(403, 'เฉพาะผู้ดูแลระบบสูงสุด (Super Admin) เท่านั้นที่สามารถสวมสิทธิ์ผู้ใช้งานได้');
        }

        if ($user->hasRole('The Creator') || $user->id === 129) {
            return redirect()->back()->with('error', 'ไม่สามารถสวมสิทธิ์ผู้ดูแลระบบสูงสุดด้วยกันเองได้');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('warning', 'ท่านกำลังใช้งานบัญชีนี้อยู่แล้ว');
        }

        if (!session()->has('impersonator_id')) {
            session()->put('impersonator_id', $currentUser->id);
            session()->put('impersonator_username', $currentUser->username);
        }

        session()->put('impersonated_user_id', $user->id);
        session()->forget('active_role_scope_id');

        \Illuminate\Support\Facades\Auth::login($user);

        \App\Models\AuditLog::record(
            event: 'impersonate',
            auditable: $user,
            remarks: "Admin '{$currentUser->username}' สวมสิทธิ์เข้าใช้งานเป็น '{$user->username}'"
        );

        return redirect()->route('backend.site.index')
            ->with('success', "ท่านกำลังเข้าใช้งานในฐานะ: '{$user->username}' (โหมดสวมสิทธิ์)");
    }

    /**
     * Stop impersonating and return to original admin account.
     */
    public function stopImpersonate(Request $request)
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('backend.user.index');
        }

        $originalUserId = session()->get('impersonator_id');
        $originalUser = User::find($originalUserId);
        $impersonatedUser = auth()->user();

        session()->forget(['impersonator_id', 'impersonator_username', 'impersonated_user_id', 'active_role_scope_id']);

        if ($originalUser) {
            \Illuminate\Support\Facades\Auth::login($originalUser);

            \App\Models\AuditLog::record(
                event: 'impersonate',
                auditable: $originalUser,
                remarks: "Admin '{$originalUser->username}' สิ้นสุดการสวมสิทธิ์ผู้ใช้ '{$impersonatedUser->username}' กลับสู่บัญชีเดิม"
            );

            return redirect()->route('backend.user.index')
                ->with('success', "สิ้นสุดการสวมสิทธิ์ กลับสู่บัญชีผู้ดูแลระบบ '{$originalUser->username}' เรียบร้อยแล้ว");
        }

        return redirect()->route('backend.user.index');
    }

    /**
     * Admin verifies and unlocks user onboarding request.
     */
    public function approveOnboarding(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        if (!$currentUser || (!$currentUser->hasRole('The Creator') && !$currentUser->hasRole('Admin') && !$currentUser->hasPermission('User Manager'))) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถอนุมัติและปลดล็อกสิทธิ์ได้');
        }

        $mode = $request->input('mode', 'create_new');

        if ($mode === 'link_existing' && $request->filled('existing_researcher_id')) {
            $researcher = RdbResearcher::findOrFail($request->existing_researcher_id);
            if ($user->onboarding_department_id && !$researcher->department_id) {
                $researcher->department_id = $user->onboarding_department_id;
                $researcher->save();
            }
        } else {
            // Create a new researcher record
            $researcher = new RdbResearcher();
            $researcher->prefix_id = $user->onboarding_prefix_id ?? 1;
            $researcher->researcher_fname = $user->onboarding_fname ?: $user->username;
            $researcher->researcher_lname = $user->onboarding_lname ?: '-';
            $researcher->researcher_fnameEN = $user->onboarding_fname_en;
            $researcher->researcher_lnameEN = $user->onboarding_lname_en;
            $researcher->department_id = $user->onboarding_department_id;
            $researcher->researcher_email = $user->email;
            $researcher->researcher_tel = $user->onboarding_tel;
            $researcher->researcher_mobile = $user->onboarding_tel;
            $researcher->user_created = auth()->id();
            $researcher->created_at = now();
            $researcher->updated_at = now();
            $researcher->save();
        }

        // Link researcher to user & mark approved
        $user->researcher_id = $researcher->researcher_id;
        $user->onboarding_status = 'approved';
        $user->onboarding_approved_at = now();
        $user->onboarding_approved_by = auth()->id();
        $user->onboarding_reject_reason = null;
        $user->save();

        // Assign Researcher role in auth_assignment if not exists
        if (class_exists(AuthAssignment::class)) {
            AuthAssignment::where('user_id', (string)$user->id)->where('item_name', 'User')->delete();

            if (!AuthAssignment::where('user_id', (string)$user->id)->where('item_name', 'Researcher')->exists()) {
                AuthAssignment::create([
                    'item_name' => 'Researcher',
                    'user_id' => (string)$user->id,
                    'created_at' => time(),
                ]);
            }
        }

        return redirect()->back()
            ->with('success', "อนุมัติและปลดล็อกสิทธิ์นักวิจัยสำหรับผู้ใช้ {$user->username} เรียบร้อยแล้ว");
    }

    /**
     * Admin rejects onboarding request with a reason.
     */
    public function rejectOnboarding(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        if (!$currentUser || (!$currentUser->hasRole('The Creator') && !$currentUser->hasRole('Admin') && !$currentUser->hasPermission('User Manager'))) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถปฏิเสธคำขอได้');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'กรุณาระบุเหตุผลการปฏิเสธคำขอ',
        ]);

        $user->onboarding_status = 'rejected';
        $user->onboarding_reject_reason = trim($request->reason);
        $user->onboarding_approved_at = null;
        $user->onboarding_approved_by = auth()->id();
        $user->save();

        return redirect()->back()
            ->with('warning', "ปฏิเสธคำขอยืนยันตัวตนของผู้ใช้ {$user->username} แล้ว (ผู้ใช้สามารถแก้ไขและส่งคำขอใหม่ได้)");
    }
}
