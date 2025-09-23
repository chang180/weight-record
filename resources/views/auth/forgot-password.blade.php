<x-guest-layout>
    <x-auth-card>
        <!-- 頁面標題 -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">忘記密碼？</h1>
            <p class="text-gray-600">沒問題，請輸入您的電子郵件，我們會發送重設密碼的連結給您</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
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

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-0.5 shadow-lg">
                {{ __('發送重設連結') }}
            </button>

            <!-- Back to Login -->
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-300">
                    ← 返回登入頁面
                </a>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
