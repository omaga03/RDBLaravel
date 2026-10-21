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

        $query = User::has('roles')->with(['roles', 'researcher.department', 'researcher.prefix']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('researcher', function ($rq) use ($search) {
                      $rq->where('researcher_fname', 'like', "%{$search}%")
                         ->orWhere('researcher_lname', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('item_name', $role);
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);
        $roles = AuthItem::where('type', 1)->orderBy('name')->get();

        return view('backend.user.index', compact('users', 'roles'));
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
}
