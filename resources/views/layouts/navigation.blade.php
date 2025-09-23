<nav class="bg-white border-b border-gray-200 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo & Links -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}" class="font-bold text-indigo-600 text-xl tracking-tight hover:text-indigo-800 transition duration-300">
                    <span class="flex items-center">
                        <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        {{ config('app.name', 'WeightRecord') }}
                    </span>
                </a>

                <div class="hidden sm:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('新增') }}
                        </span>
                    </a>
                    <a href="{{ route('record') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('record') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('記錄') }}
                        </span>
                    </a>
                    <a href="{{ route('chart') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('chart') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                            {{ __('圖表') }}
                        </span>
                    </a>
                    <a href="{{ route('analysis.trend') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('analysis.trend') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ __('趨勢') }}
                        </span>
                    </a>
                    <a href="{{ route('analysis.health') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('analysis.health') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            {{ __('健康') }}
                        </span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('profile.edit') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition duration-200">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('設定') }}
                        </span>
                    </a>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="sm:hidden">
                <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-indigo-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                    <span class="sr-only">開啟選單</span>
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- User Info & Logout -->
            <div class="hidden sm:flex items-center space-x-4">
                <div class="text-right">
                    <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow hover:bg-indigo-700 transition duration-300 transform hover:-translate-y-1">
                        {{ __('登出') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="mobile-menu hidden sm:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('新增') }}
            </a>
            <a href="{{ route('record') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('record') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('記錄') }}
            </a>
            <a href="{{ route('chart') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('chart') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('圖表') }}
            </a>
            <a href="{{ route('analysis.trend') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('analysis.trend') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('趨勢') }}
            </a>
            <a href="{{ route('analysis.health') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('analysis.health') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('健康') }}
            </a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('profile.edit') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium">
                {{ __('設定') }}
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4 flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-200 flex items-center justify-center text-indigo-600 font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow hover:bg-indigo-700 transition">
                        {{ __('登出') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    // 手機選單切換
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
