<x-guest-layout>
    <x-auth-card>
        <!-- 頁面標題 -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">歡迎回來</h1>
            <p class="text-gray-600">登入您的帳戶以繼續使用體重記錄系統</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-6">
                <x-label for="email" :value="__('電子郵件')" class="text-sm font-medium text-gray-700 mb-2" />
                <x-input id="email" 
                         class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300" 
                         type="email" 
                         name="email" 
                         :value="old('email')" 
                         required 
                         autofocus 
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
                         autocomplete="current-password"
                         placeholder="請輸入您的密碼" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" 
                           type="checkbox" 
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                           name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('記住我') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition duration-300" 
                       href="{{ route('password.request') }}">
                        {{ __('忘記密碼?') }}
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-0.5 shadow-lg">
                {{ __('登入') }}
            </button>

            <!-- Register Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    還沒有帳戶？
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-300">
                        立即註冊
                    </a>
                </p>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
