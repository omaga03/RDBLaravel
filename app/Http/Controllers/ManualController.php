<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualController extends Controller
{
    /**
     * Display the Manuals Index / Hub (คู่มือทั้งหมด)
     */
    public function index(): Response
    {
        return $this->serveManual('index.html');
    }

    /**
     * Display Volume 1: Frontend User Manual (คู่มือหน้าบ้าน)
     */
    public function frontend(): Response
    {
        return $this->serveManual('VOLUME_1_FRONTEND_USER_MANUAL.html');
    }

    /**
     * Display Volume 2: Backend Admin & Researcher Manual (คู่มือหลังบ้าน)
     */
    public function backend(): Response
    {
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
    public function show(string $file): Response
    {
        $safeFile = basename($file);
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

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
