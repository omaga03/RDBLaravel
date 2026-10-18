<x-guest-layout>
    <!-- Card Header / Title -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mb-3 shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800">
            ลงทะเบียนเข้าใช้งานระบบ
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            สิทธิพิเศษสำหรับคณาจารย์ นักวิจัย และบุคลากร มรภ.เพชรบูรณ์
        </p>
    </div>

    <!-- Session Status / Alerts -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-start gap-2 shadow-sm">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl flex items-start gap-2 shadow-sm">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    <!-- Policy Banner: Google Only Registration -->
    <div class="mb-5 p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs sm:text-sm text-slate-700 leading-relaxed">
        <div class="flex items-center gap-2 font-semibold text-blue-900 mb-1.5">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>การยืนยันตัวตนผ่านบัญชีมหาวิทยาลัย</span>
        </div>
        <p class="text-slate-600">
            เพื่อความปลอดภัยและการคุ้มครองลิขสิทธิ์งานวิจัย ระบบเปิดให้ลงทะเบียนเฉพาะผู้มีบัญชี <strong class="text-blue-800 font-semibold">Google Workspace (@pcru.ac.th)</strong> เท่านั้น โดยระบบจะสร้างและเชื่อมโยงบัญชีนักวิจัยให้อัตโนมัติทันที
        </p>
    </div>

    <!-- Primary Action: Google Workspace SSO Button -->
    <div class="mb-6">
        <a href="{{ route('auth.google') }}" 
           class="group w-full flex items-center justify-center gap-3 px-4 py-3.5 bg-white hover:bg-blue-50/40 text-slate-800 font-medium text-base rounded-2xl border-2 border-slate-200 hover:border-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
            <!-- Official Google 'G' Logo -->
            <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
            </svg>
            <div class="text-left">
                <div class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors text-sm sm:text-base">
                    ลงทะเบียนด้วย Google Workspace
                </div>
                <div class="text-xs text-slate-500 font-light">
                    ใช้อีเมล @pcru.ac.th หรือบัญชี Google
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all ms-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <!-- Feature Highlights -->
    <div class="space-y-2.5 mb-6 pt-2 border-t border-slate-100 text-xs text-slate-600">
        <div class="flex items-center gap-2.5">
            <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold">✓</div>
            <span>เข้าถึงรายงานวิจัยฉบับสมบูรณ์และเอกสารแนบทุกโครงการ</span>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold">✓</div>
            <span>จัดการโปรไฟล์นักวิจัย ผลงานตีพิมพ์ และทรัพย์สินทางปัญญา</span>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold">✓</div>
            <span>ปลอดภัย ไม่ต้องตั้งรหัสผ่านใหม่ ยืนยันตัวตนผ่าน SSO มหาวิทยาลัย</span>
        </div>
    </div>

    <!-- Footer Links -->
    <div class="pt-4 border-t border-slate-100 text-center space-y-2">
        <div class="text-sm text-slate-600">
            มีบัญชีผู้ใช้งานอยู่แล้ว?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline ms-1">
                เข้าสู่ระบบที่นี่
            </a>
        </div>
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 transition-colors">
                <span>← กลับสู่หน้าหลักระบบสารสนเทศวิจัย</span>
            </a>
        </div>
    </div>
</x-guest-layout>
