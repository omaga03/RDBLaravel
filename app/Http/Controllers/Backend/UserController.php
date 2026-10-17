<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuthItem;
use App\Models\AuthAssignment;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:auth_item,name'],
            'researcher_id' => ['nullable', 'exists:rdb_researcher,researcher_id'],
            'status' => ['required', 'integer'],
        ]);

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

        $roles = AuthItem::where('type', 1)->orderBy('name')->get();
        $researchers = RdbResearcher::with(['prefix', 'department'])->orderBy('researcher_fname')->get();
        $userRole = $user->roles()->first()?->item_name;

        return view('backend.user.edit', compact('user', 'roles', 'researchers', 'userRole'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('user')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('user')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:auth_item,name'],
            'researcher_id' => ['nullable', 'exists:rdb_researcher,researcher_id'],
            'status' => ['required', 'integer'],
        ]);

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }
        $user->status = $validated['status'];
        $user->researcher_id = $validated['researcher_id'] ?? null;
        $user->user_updated = auth()->id();
        $user->save();

        // Sync Role
        AuthAssignment::where('user_id', $user->id)->delete();
        AuthAssignment::create([
            'item_name' => $validated['role'],
            'user_id' => $user->id,
            'created_at' => time(),
        ]);

        return redirect()->route('backend.user.index')
            ->with('success', 'ปรับปรุงข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('backend.user.index')
                ->with('error', 'ไม่สามารถลบบัญชีผู้ใช้ที่กำลังเข้าสู่ระบบอยู่ได้');
        }

        if ($user->id === 1) {
            return redirect()->route('backend.user.index')
                ->with('error', 'ไม่สามารถลบบัญชีผู้ดูแลระบบหลัก (ID: 1) ได้');
        }

        AuthAssignment::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('backend.user.index')
            ->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }
}
