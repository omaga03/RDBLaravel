<x-guest-layout>
    <!-- Card Header / Title -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mb-3 shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800">
            เข้าสู่ระบบสารสนเทศวิจัย
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Research Database Management System (PCRU RDB)
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

    <!-- Primary Login Option: Google Workspace SSO -->
    <div class="mb-5">
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
                    เข้าสู่ระบบด้วย Google Workspace
                </div>
                <div class="text-xs text-slate-500 font-light">
                    อีเมลมหาวิทยาลัย @pcru.ac.th หรือบัญชี Google
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all ms-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <!-- Divider: OR -->
    <div class="relative my-5">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-white px-3 text-slate-400 font-normal">หรือเข้าสู่ระบบด้วยชื่อผู้ใช้</span>
        </div>
    </div>

    <!-- Collapsible Toggle Button for Standard Login -->
    <div class="mb-4">
        <button type="button" 
                onclick="toggleStandardLogin()" 
                id="toggleLoginBtn"
                class="w-full py-2.5 px-4 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition-all duration-200 flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>เข้าสู่ระบบด้วยชื่อผู้ใช้และรหัสผ่าน</span>
            </span>
            <svg id="toggleChevron" class="w-4 h-4 text-slate-400 transform transition-transform duration-200 {{ ($errors->any() || old('username')) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <!-- Standard Login Form (Hidden by default, shown if toggle clicked or on validation errors) -->
    <div id="standardLoginForm" class="{{ ($errors->any() || old('username')) ? '' : 'hidden' }} transition-all duration-300">
        <form method="POST" action="{{ route('login') }}" class="pt-2 space-y-4">
            @csrf

            <!-- Username -->
            <div>
                <label for="username" class="block font-medium text-xs sm:text-sm text-slate-700 mb-1">
                    <span>👤 {{ __('Username') }} / ชื่อผู้ใช้</span>
                </label>
                <input id="username" 
                       type="text" 
                       name="username" 
                       value="{{ old('username') }}" 
                       autofocus 
                       autocomplete="username" 
                       placeholder="กรอกชื่อผู้ใช้..."
                       class="block w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm" />
                <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block font-medium text-xs sm:text-sm text-slate-700 mb-1">
                    <span>🔐 {{ __('Password') }} / รหัสผ่าน</span>
                </label>
                <div class="relative flex items-center">
                    <input id="password" 
                           type="password" 
                           name="password" 
                           autocomplete="current-password" 
                           placeholder="กรอกรหัสผ่าน..."
                           class="block w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm pe-10" />
                    <button type="button" 
                            onclick="togglePassword()" 
                            class="absolute end-2 p-1.5 text-slate-400 hover:text-slate-600 focus:outline-none transition-colors" 
                            title="แสดง/ซ่อนรหัสผ่าน">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-xs sm:text-sm">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                    <span class="ms-2 text-slate-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs text-slate-500 hover:text-blue-600 hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 bg-gradient-to-r from-slate-900 to-slate-800 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold text-sm rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                    <span>🚀 เข้าสู่ระบบ (Log in)</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Footer Links -->
    <div class="pt-5 mt-6 border-t border-slate-100 text-center space-y-2">
        <div class="text-sm text-slate-600">
            ยังไม่มีบัญชีผู้ใช้งาน?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline ms-1">
                ลงทะเบียนผ่าน Google Workspace
            </a>
        </div>
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 transition-colors">
                <span>← กลับสู่หน้าหลักระบบสารสนเทศวิจัย</span>
            </a>
        </div>
    </div>

    <script>
        function toggleStandardLogin() {
            const form = document.getElementById('standardLoginForm');
            const chevron = document.getElementById('toggleChevron');
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                form.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>
