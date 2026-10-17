<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuthAssignment;
use App\Models\AuthItem;
use App\Models\AuthItemChild;
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
        $roles = AuthItem::where('type', 1)->orderBy('name')->get();

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

        AuthItem::create($data);

        return redirect()->route('backend.rbac.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('backend.rbac.edit', $id);
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
}