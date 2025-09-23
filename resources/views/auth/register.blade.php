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
