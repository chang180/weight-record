<x-guest-layout>
    <x-auth-card>
        <!-- 頁面標題 -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">建立新帳戶</h1>
            <p class="text-gray-600">開始您的健康管理之旅，記錄每一天的進步</p>
        </div>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <x-label for="name" :value="__('姓名')" class="text-sm font-medium text-gray-700 mb-2" />
                <x-input id="name" 
                         class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300" 
                         type="text" 
                         name="name" 
                         :value="old('name')" 
                         required 
                         autofocus 
                         placeholder="請輸入您的姓名" />
            </div>

            <!-- Email Address -->
            <div class="mb-6">
                <x-label for="email" :value="__('電子郵件')" class="text-sm font-medium text-gray-700 mb-2" />
                <x-input id="email" 
                         class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300" 
                         type="email" 
                         name="email" 
                         :value="old('email')" 
                         required 
                         placeholder="請輸入您的電子郵件" />
            </div>

            <!-- Password -->
            <div class="mb-6">
                <x-label for="password" :value="__('密碼')" class="text-sm font-medium text-gray-700 mb-2" />
                <x-input id="password" 
                         class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300"
                         type="password"
                         name="password"
                         required 
                         autocomplete="new-password"
                         placeholder="請輸入密碼（至少8個字符）" />
                <p class="text-xs text-gray-500 mt-1">密碼至少需要8個字符</p>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <x-label for="password_confirmation" :value="__('確認密碼')" class="text-sm font-medium text-gray-700 mb-2" />
                <x-input id="password_confirmation" 
                         class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300"
                         type="password"
                         name="password_confirmation" 
                         required 
                         placeholder="請再次輸入密碼" />
            </div>

            <!-- Terms Agreement -->
            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" 
                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                           required>
                    <span class="ml-2 text-sm text-gray-600">
                        我同意
                        <a href="/terms" class="text-indigo-600 hover:text-indigo-800 font-medium">使用條款</a>
                        和
                        <a href="/privacy" class="text-indigo-600 hover:text-indigo-800 font-medium">隱私政策</a>
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-0.5 shadow-lg">
                {{ __('建立帳戶') }}
            </button>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">或</span>
                </div>
            </div>

            <!-- Google Register Button -->
            <a href="{{ route('auth.google') }}" 
               class="w-full flex items-center justify-center gap-3 bg-white border-2 border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-0.5 shadow-md">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>使用 Google 註冊</span>
            </a>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    已經有帳戶了？
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-300">
                        立即登入
                    </a>
                </p>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
