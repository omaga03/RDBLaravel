<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view (Google Workspace SSO only).
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming manual registration request.
     * Manual direct registration is disabled; only Google Workspace SSO is permitted.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('register')->with('warning', 'ระบบเปิดให้ลงทะเบียนเข้าใช้งานผ่านบัญชี Google Workspace (@pcru.ac.th) เท่านั้น โปรดคลิกปุ่มลงทะเบียนด้วย Google ด้านล่าง');
    }
}
