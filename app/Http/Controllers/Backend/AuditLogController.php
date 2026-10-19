<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('The Creator') && $currentUser->id !== 129 && !$currentUser->hasRole('Admin Research')) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถดูประวัติการใช้งานระบบได้');
        }

        $query = AuditLog::query();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('diff_summary', 'like', "%{$search}%")
                  ->orWhere('auditable_id', $search);
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $events = AuditLog::select('event')->distinct()->pluck('event');
        $types = AuditLog::select('auditable_type')->distinct()->pluck('auditable_type');
        $users = User::has('roles')->select('id', 'username')->orderBy('username')->get();

        return view('backend.audit_log.index', compact('logs', 'events', 'types', 'users'));
    }

    public function show($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('The Creator') && $currentUser->id !== 129 && !$currentUser->hasRole('Admin Research')) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถดูประวัติการใช้งานระบบได้');
        }

        $log = AuditLog::findOrFail($id);

        return view('backend.audit_log.show', compact('log'));
    }
}
