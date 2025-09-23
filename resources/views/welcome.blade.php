<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Weight Record</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* 備用樣式，確保基本顯示正常 */
        .hero-bg {
            background-image: linear-gradient(to right, #4F46E5, #7C3AED);
        }
        /* 確保 Tailwind 類別在 CSS 未載入時也能正常顯示 */
        .min-h-screen { min-height: 100vh; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .bg-cover { background-size: cover; }
        .bg-center { background-position: center; }
        .bg-black { background-color: black; }
        .bg-opacity-50 { opacity: 0.5; }
        .z-10 { z-index: 10; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .text-white { color: white; }
        .text-center { text-align: center; }
    </style>
</head>
<body class="antialiased font-sans">
    <!-- 英雄區塊 -->
    <div class="relative min-h-screen hero-bg">
        <!-- 背景圖片 - 嘗試多種可能的路徑 -->
        <div class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ asset('storage/cover.jpg') }}'), url('{{ asset('img/cover.jpg') }}'), url('{{ asset('images/cover.jpg') }}'); filter: brightness(0.7);">
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- 導航欄 -->
        <div class="absolute top-0 w-full z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-white">📊 體重記錄</div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-white hover:text-blue-200 font-medium transition duration-300">儀表板</a>
                                <a href="{{ route('record') }}" class="text-white hover:text-blue-200 font-medium transition duration-300">記錄管理</a>
                                <a href="{{ route('chart') }}" class="text-white hover:text-blue-200 font-medium transition duration-300">統計圖表</a>
                            @else
                                <a href="{{ route('login') }}" class="text-white hover:text-blue-200 font-medium transition duration-300">登入</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-300">免費註冊</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要內容 -->
        <div class="min-h-screen flex items-center justify-center">
            <div class="relative z-10 text-center px-8 max-w-3xl">
                <h1 class="text-5xl font-extrabold text-white mb-6 drop-shadow-lg">簡易體重記錄器</h1>
                <p class="text-2xl text-white mb-8 drop-shadow-md">追蹤您的健康旅程，記錄每一步的進展</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-md transition duration-300 transform hover:-translate-y-1 shadow-lg">進入主面板</a>
                        @else
                            <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-md transition duration-300 transform hover:-translate-y-1 shadow-lg">登入</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 px-6 rounded-md transition duration-300 transform hover:-translate-y-1 shadow-lg">註冊</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 功能特色區域 -->
    <section class="bg-gray-50 py-20 px-8">
        <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">為什麼選擇我們的體重記錄器？</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                <div class="text-4xl mb-6 text-indigo-600">📊</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">簡單易用的數據追蹤</h3>
                <p class="text-gray-600 leading-relaxed">輕鬆記錄您的每日體重，查看趨勢變化，掌握健康狀況。</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                <div class="text-4xl mb-6 text-indigo-600">📱</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">隨時隨地訪問</h3>
                <p class="text-gray-600 leading-relaxed">無論在家還是外出，都可以通過任何設備記錄和查看您的體重數據。</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                <div class="text-4xl mb-6 text-indigo-600">🔒</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">安全私密</h3>
                <p class="text-gray-600 leading-relaxed">您的健康數據受到嚴格保護，只有您能夠訪問。</p>
            </div>
        </div>
    </section>

    <!-- 實際功能展示 -->
    <section class="py-20 px-8 bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">實際功能展示</h2>
                <p class="text-xl text-gray-600">看看我們的系統如何幫助您管理健康</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6">📊 智能圖表分析</h3>
                    <p class="text-lg text-gray-600 mb-8">系統會自動將您的體重數據轉換成直觀的圖表，讓您清楚看到體重變化趨勢。</p>
                    <ul class="space-y-4">
                        <li class="flex items-center text-gray-700">
                            <span class="text-green-500 mr-3">✓</span>
                            自動生成趨勢圖表
                        </li>
                        <li class="flex items-center text-gray-700">
                            <span class="text-green-500 mr-3">✓</span>
                            支援多種圖表類型
                        </li>
                        <li class="flex items-center text-gray-700">
                            <span class="text-green-500 mr-3">✓</span>
                            響應式設計，手機也能完美顯示
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-100 rounded-2xl p-8 text-center">
                    <div class="text-6xl mb-4">📈</div>
                    <h4 class="text-xl font-bold text-gray-900 mb-4">圖表功能預覽</h4>
                    <p class="text-gray-600 mb-6">基於 Chart.js 的專業圖表，支援多種數據視覺化方式</p>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('chart') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-300">
                                查看我的圖表 →
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-300">
                                立即體驗 →
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- 開始使用區域 -->
    <section class="py-20 px-8 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-white mb-6">準備開始您的健康之旅？</h2>
            <p class="text-xl text-white mb-12">立即註冊，開始記錄您的體重變化，掌握健康趨勢</p>
            
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-white text-indigo-600 font-bold py-4 px-8 rounded-xl text-lg transition duration-300 transform hover:-translate-y-2 hover:shadow-2xl inline-block">
                        進入儀表板 →
                    </a>
                @else
                    <div class="flex flex-col sm:flex-row justify-center gap-6">
                        <a href="{{ route('register') }}" class="bg-white text-indigo-600 font-bold py-4 px-8 rounded-xl text-lg transition duration-300 transform hover:-translate-y-2 hover:shadow-2xl">
                            免費註冊 →
                        </a>
                        <a href="{{ route('login') }}" class="border-2 border-white text-white font-bold py-4 px-8 rounded-xl text-lg transition duration-300 transform hover:-translate-y-2 hover:bg-white hover:text-indigo-600">
                            已有帳號？登入
                        </a>
                    </div>
                @endauth
            @endif
        </div>
    </section>

    <!-- 頁尾 -->
    <footer class="bg-gray-800 text-white py-8 px-4 text-center">
        <p class="mb-4">© {{ date('Y') }} 體重記錄器 - 保持健康的最佳夥伴</p>
        <div class="space-x-4">
            <a href="/privacy" class="text-gray-400 hover:text-white transition duration-300">隱私政策</a>
            <span class="text-gray-600">|</span>
            <a href="/terms" class="text-gray-400 hover:text-white transition duration-300">使用條款</a>
            <span class="text-gray-600">|</span>
            <a href="/contact" class="text-gray-400 hover:text-white transition duration-300">聯絡我們</a>
        </div>
    </footer>
</body>
</html>
