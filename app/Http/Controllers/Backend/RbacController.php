<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuthAssignment;
use App\Models\AuthItem;
use App\Models\AuthItemChild;
use App\Services\RbacService;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\StoreRbacRoleRequest;
use App\Http\Requests\Backend\UpdateRbacRoleRequest;

class RbacController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', AuthItem::class);
        RbacService::ensurePermissionsExist();

        $roles = AuthItem::where('type', 1)
            ->withCount('children')
            ->orderBy('name')
            ->get();

        return view('backend.rbac.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', AuthItem::class);
        return view('backend.rbac.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRbacRoleRequest $request)
    {
        $this->authorize('create', AuthItem::class);
        $data = $request->validated();
        $data['type'] = 1; // Always create a Role
        $data['created_at'] = time();
        $data['updated_at'] = time();

        $role = AuthItem::create($data);

        return redirect()->route('backend.rbac.permissions', $role->name)
            ->with('success', "สร้างบทบาท '{$role->name}' เรียบร้อยแล้ว กรุณากำหนดสิทธิ์การเข้าถึง");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('backend.rbac.permissions', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($rbac)
    {
        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $this->authorize('update', $item);
        return view('backend.rbac.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRbacRoleRequest $request, $rbac)
    {
        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $this->authorize('update', $item);
        $validated = $request->validated();

        $item->update([
            'description' => $validated['description'] ?? null,
            'updated_at' => time(),
        ]);

        return redirect()->route('backend.rbac.index')
            ->with('success', 'แก้ไขบทบาทเรียบร้อยแล้ว');
    }

    /**
     * Show the form for managing role permissions (Permission Matrix).
     */
    public function permissions($rbac)
    {
        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $this->authorize('update', $item);

        RbacService::ensurePermissionsExist();

        $permissionGroups = RbacService::getPermissionsGrouped();
        $assignedPermissions = RbacService::getAssignedPermissions($item->name);

        return view('backend.rbac.permissions', compact('item', 'permissionGroups', 'assignedPermissions'));
    }

    /**
     * Update permissions for a role.
     */
    public function updatePermissions(Request $request, $rbac)
    {
        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $this->authorize('update', $item);

        $selectedPermissions = $request->input('permissions', []);
        RbacService::syncRolePermissions($item->name, $selectedPermissions);

        return redirect()->route('backend.rbac.permissions', $item->name)
            ->with('success', "บันทึกการกำหนดสิทธิ์สำหรับบทบาท '{$item->name}' เรียบร้อยแล้ว");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($rbac)
    {
        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $this->authorize('delete', $item);
        $coreRoles = ['The Creator', 'Admin', 'Admin Project', 'Admin Published', 'Admin Dip', 'Admin Utilize', 'Staff', 'Researcher'];
        if (in_array($item->name, $coreRoles)) {
            return redirect()->route('backend.rbac.index')
                ->with('error', 'ไม่สามารถลบบทบาทหลักของระบบได้');
        }

        // Delete all assignments of this role to users
        AuthAssignment::where('item_name', $item->name)->delete();

        // Delete child relationships
        AuthItemChild::where('parent', $item->name)->orWhere('child', $item->name)->delete();

        // Finally, delete the item itself
        $item->delete();

        return redirect()->route('backend.rbac.index')
            ->with('success', 'ลบบทบาทเรียบร้อยแล้ว');
    }

    /**
     * Impersonate a user with the specified role for testing purposes.
     */
    public function impersonate(Request $request, $rbac)
    {
        $currentUser = auth()->user();
        
        // Authorization: Only 'The Creator' or already impersonating admin can switch roles
        if (!$currentUser->hasRole('The Creator') && $currentUser->id !== 129 && !session()->has('impersonator_id')) {
            abort(403, 'เฉพาะผู้ดูแลระบบระดับสูงสุด (The Creator) เท่านั้นที่สามารถใช้ฟังก์ชันทดสอบบทบาทได้');
        }

        $item = $rbac instanceof AuthItem ? $rbac : AuthItem::where('name', $rbac)->firstOrFail();
        $roleName = $item->name;

        // Find an active user with this role (excluding current user if possible)
        $targetUser = \App\Models\User::whereHas('roles', function ($q) use ($roleName) {
            $q->where('item_name', $roleName);
        })
        ->where('id', '!=', $currentUser->id)
        ->first();

        // If no other user with this role exists, find any user with this role
        if (!$targetUser) {
            $targetUser = \App\Models\User::whereHas('roles', function ($q) use ($roleName) {
                $q->where('item_name', $roleName);
            })->first();
        }

        // If still no user exists with this role, create a test dummy user for testing
        if (!$targetUser) {
            $slug = \Illuminate\Support\Str::slug($roleName, '_');
            $testUsername = 'test_' . $slug;
            $targetUser = \App\Models\User::where('username', $testUsername)->first();

            if (!$targetUser) {
                $targetUser = \App\Models\User::create([
                    'username' => $testUsername,
                    'email' => $slug . '@test.local',
                    'password' => 'TestRole123!',
                    'status' => 10,
                ]);
            }

            // Assign the role to this test user
            AuthAssignment::firstOrCreate([
                'item_name' => $roleName,
                'user_id' => $targetUser->id,
            ], [
                'created_at' => time(),
            ]);
        }

        // If not already in an impersonation session, save original user ID
        if (!session()->has('impersonator_id')) {
            session()->put('impersonator_id', $currentUser->id);
            session()->put('impersonator_username', $currentUser->username);
        }

        session()->put('impersonated_role', $roleName);

        \Illuminate\Support\Facades\Auth::login($targetUser);

        return redirect()->route('backend.site.index')
            ->with('success', "เข้าสู่ระบบในบทบาท '{$roleName}' เรียบร้อยแล้ว (ทดสอบในฐานะผู้ใช้งาน: {$targetUser->username})");
    }

    /**
     * Stop impersonating and return to the original administrator account.
     */
    public function stopImpersonate(Request $request)
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('backend.rbac.index');
        }

        $originalUserId = session()->get('impersonator_id');
        $originalUser = \App\Models\User::find($originalUserId);

        session()->forget(['impersonator_id', 'impersonator_username', 'impersonated_role']);

        if ($originalUser) {
            \Illuminate\Support\Facades\Auth::login($originalUser);
            return redirect()->route('backend.rbac.index')
                ->with('success', "สิ้นสุดการทดสอบบทบาท กลับสู่บัญชีหลัก '{$originalUser->username}' เรียบร้อยแล้ว");
        }

        return redirect()->route('backend.rbac.index');
    }
}