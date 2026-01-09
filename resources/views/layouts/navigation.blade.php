<nav class="bg-white/95 backdrop-blur-sm border-b border-gray-200/50 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo & Links -->
            <div class="flex items-center flex-1 min-w-0">
                <a href="{{ route('dashboard') }}" class="font-bold text-indigo-600 text-xl tracking-tight hover:text-indigo-800 transition duration-300 flex-shrink-0">
                    <span class="flex items-center">
                        <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ config('app.name', 'WeightRecord') }}</span>
                        <span class="sm:hidden">WR</span>
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center gap-1 ml-8 overflow-x-auto scrollbar-hide">
                    <!-- 主要功能 -->
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('新增') }}
                        </span>
                    </a>
                    <a href="{{ route('record') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('record') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('記錄') }}
                        </span>
                    </a>
                    <a href="{{ route('chart') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('chart') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                            {{ __('圖表') }}
                        </span>
                    </a>
                    
                    <!-- 分隔線 -->
                    <div class="h-6 w-px bg-gray-300 mx-1"></div>
                    
                    <!-- 分析功能 -->
                    <a href="{{ route('analysis.trend') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('analysis.trend') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ __('趨勢') }}
                        </span>
                    </a>
                    <a href="{{ route('analysis.health') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('analysis.health') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            {{ __('健康') }}
                        </span>
                    </a>
                    
                    <!-- 分隔線 -->
                    <div class="h-6 w-px bg-gray-300 mx-1"></div>
                    
                    <!-- 遊戲化功能 -->
                    <a href="{{ route('daily-logs.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('daily-logs.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <span class="text-base">📋</span>
                            {{ __('任務') }}
                        </span>
                    </a>
                    <a href="{{ route('achievements.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('achievements.*') ? 'bg-yellow-100 text-yellow-700 shadow-sm' : 'text-gray-700 hover:bg-yellow-50 hover:text-yellow-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <span class="text-base">🏆</span>
                            {{ __('成就') }}
                        </span>
                    </a>
                    <a href="{{ route('rewards.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('rewards.*') ? 'bg-green-100 text-green-700 shadow-sm' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <span class="text-base">🎁</span>
                            {{ __('獎勵') }}
                        </span>
                    </a>
                    <a href="{{ route('gamification.stats') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('gamification.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <span class="text-base">📊</span>
                            {{ __('統計') }}
                        </span>
                    </a>
                    
                    <!-- 分隔線 -->
                    <div class="h-6 w-px bg-gray-300 mx-1"></div>
                    
                    <!-- 設定 -->
                    <a href="{{ route('profile.edit') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200 text-sm whitespace-nowrap">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('設定') }}
                        </span>
                    </a>
                </div>
                
                <!-- Tablet Navigation (簡化版) -->
                <div class="hidden md:flex lg:hidden items-center gap-1 ml-4 overflow-x-auto scrollbar-hide">
                    <a href="{{ route('dashboard') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }} transition-all duration-200 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </a>
                    <a href="{{ route('record') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('record') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }} transition-all duration-200 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </a>
                    <a href="{{ route('chart') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('chart') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }} transition-all duration-200 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('daily-logs.index') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('daily-logs.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-700 hover:bg-purple-50' }} transition-all duration-200 text-xs">
                        📋
                    </a>
                    <a href="{{ route('achievements.index') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('achievements.*') ? 'bg-yellow-100 text-yellow-700' : 'text-gray-700 hover:bg-yellow-50' }} transition-all duration-200 text-xs">
                        🏆
                    </a>
                    <a href="{{ route('rewards.index') }}" class="px-2.5 py-2 rounded-lg {{ request()->routeIs('rewards.*') ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50' }} transition-all duration-200 text-xs">
                        🎁
                    </a>
                </div>
            </div>

            <!-- User Info & Logout (Desktop) -->
            <div class="hidden lg:flex items-center gap-4 flex-shrink-0">
                <div class="text-right hidden xl:block">
                    <div class="font-semibold text-gray-800 text-sm">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate max-w-[120px]">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-300 transform hover:scale-105 active:scale-95 text-sm">
                        {{ __('登出') }}
                    </button>
                </form>
            </div>

            <!-- Mobile menu button -->
            <div class="lg:hidden flex items-center gap-2">
                <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-indigo-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-colors" aria-expanded="false">
                    <span class="sr-only">開啟選單</span>
                    <svg class="block h-6 w-6 menu-icon-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="hidden h-6 w-6 menu-icon-close" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="mobile-menu hidden lg:hidden border-t border-gray-200 bg-white/98 backdrop-blur-sm max-h-[calc(100vh-4rem)] overflow-y-auto">
        <div class="px-4 pt-4 pb-2 space-y-1">
            <!-- 主要功能區塊 -->
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-3">主要功能</div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('新增') }}
                </a>
                <a href="{{ route('record') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('record') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    {{ __('記錄') }}
                </a>
                <a href="{{ route('chart') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('chart') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    {{ __('圖表') }}
                </a>
            </div>

            <!-- 分析功能區塊 -->
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-3">分析</div>
                <a href="{{ route('analysis.trend') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('analysis.trend') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    {{ __('趨勢') }}
                </a>
                <a href="{{ route('analysis.health') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('analysis.health') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    {{ __('健康') }}
                </a>
            </div>

            <!-- 遊戲化功能區塊 -->
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-3">遊戲化</div>
                <a href="{{ route('daily-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('daily-logs.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }} font-medium transition-all duration-200">
                    <span class="text-xl">📋</span>
                    {{ __('任務') }}
                </a>
                <a href="{{ route('achievements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('achievements.*') ? 'bg-yellow-100 text-yellow-700' : 'text-gray-700 hover:bg-yellow-50 hover:text-yellow-600' }} font-medium transition-all duration-200">
                    <span class="text-xl">🏆</span>
                    {{ __('成就') }}
                </a>
                <a href="{{ route('rewards.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('rewards.*') ? 'bg-green-100 text-green-700' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }} font-medium transition-all duration-200">
                    <span class="text-xl">🎁</span>
                    {{ __('獎勵') }}
                </a>
                <a href="{{ route('gamification.stats') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('gamification.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }} font-medium transition-all duration-200">
                    <span class="text-xl">📊</span>
                    {{ __('統計') }}
                </a>
            </div>

            <!-- 設定 -->
            <div class="mb-4">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }} font-medium transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('設定') }}
                </a>
            </div>
        </div>

        <!-- 用戶資訊區塊 -->
        <div class="pt-4 pb-4 border-t border-gray-200 bg-gray-50/50">
            <div class="px-4 flex items-center mb-3">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <div class="text-base font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="px-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-300 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        {{ __('登出') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<style>
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    // 手機選單切換
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuIconOpen = document.querySelector('.menu-icon-open');
        const menuIconClose = document.querySelector('.menu-icon-close');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                const isHidden = mobileMenu.classList.contains('hidden');
                
                if (isHidden) {
                    mobileMenu.classList.remove('hidden');
                    if (menuIconOpen) menuIconOpen.classList.add('hidden');
                    if (menuIconClose) menuIconClose.classList.remove('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'true');
                } else {
                    mobileMenu.classList.add('hidden');
                    if (menuIconOpen) menuIconOpen.classList.remove('hidden');
                    if (menuIconClose) menuIconClose.classList.add('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
            });

            // 點擊選單外部關閉選單
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        if (menuIconOpen) menuIconOpen.classList.remove('hidden');
                        if (menuIconClose) menuIconClose.classList.add('hidden');
                        mobileMenuButton.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }
    });
</script>
