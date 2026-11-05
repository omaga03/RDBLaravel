<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualController extends Controller
{
    /**
     * Display the Manuals Index / Hub (คู่มือทั้งหมด - เฉพาะผู้เข้าสู่ระบบ)
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('warning', 'กรุณาเข้าสู่ระบบเพื่อเข้าใช้งานคู่มือระบบหลังบ้าน');
        }

        return $this->serveManual('index.html');
    }

    /**
     * Display Volume 1: Frontend User Manual (คู่มือหน้าบ้าน - สาธารณะ)
     */
    public function frontend(): Response
    {
        return $this->serveManual('VOLUME_1_FRONTEND_USER_MANUAL.html');
    }

    /**
     * Display Volume 2: Backend Admin & Researcher Manual (คู่มือหลังบ้าน - เฉพาะผู้เข้าสู่ระบบ)
     */
    public function backend()
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('warning', 'กรุณาเข้าสู่ระบบเพื่อเข้าใช้งานคู่มือระบบหลังบ้าน');
        }

        return $this->serveManual('VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html');
    }

    /**
     * Display a manual screenshot image
     */
    public function screenshot(string $file): Response
    {
        $safeFile = basename($file);
        $path = base_path('docs/manuals/screenshots/' . $safeFile);

        if (!file_exists($path)) {
            abort(404, 'ไม่พบภาพประกอบคู่มือ');
        }

        return response()->file($path);
    }

    /**
     * Display a specific manual file
     */
    public function show(string $file)
    {
        $safeFile = basename($file);

        // Only Volume 1 Frontend manual is public. All other manuals require authentication.
        if ($safeFile !== 'VOLUME_1_FRONTEND_USER_MANUAL.html') {
            if (!auth()->check()) {
                return redirect()->guest(route('login'))->with('warning', 'กรุณาเข้าสู่ระบบเพื่อเข้าใช้งานคู่มือระบบหลังบ้าน');
            }
        }

        return $this->serveManual($safeFile);
    }

    /**
     * Helper to safely serve HTML manual file
     */
    protected function serveManual(string $fileName): Response
    {
        $safeFile = basename($fileName);
        $path = base_path('docs/manuals/' . $safeFile);

        if (!file_exists($path)) {
            abort(404, 'ไม่พบเอกสารคู่มือการใช้งานระบบ');
        }

        $html = file_get_contents($path);

        // If serving index.html or when accessed via base route, inject <base> so all relative links and screenshots resolve correctly
        if ($safeFile === 'index.html' || request()->is('manuals') || request()->is('backend/manuals')) {
            $baseHref = url('manuals') . '/';
            if (stripos($html, '<base ') === false) {
                $html = preg_replace('/<head>/i', "<head>\n    <base href=\"{$baseHref}\">", $html, 1);
            }
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
