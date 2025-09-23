<div class="min-h-screen flex">
    <!-- 左側背景 -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 relative">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        <div class="relative z-10 flex flex-col justify-center items-center text-white px-16 py-20 max-w-lg mx-auto">
            <div class="text-6xl mb-8">📊</div>
            <h1 class="text-4xl font-bold mb-6 text-center">體重記錄系統</h1>
            <p class="text-xl text-center leading-relaxed mb-10">
                開始您的健康管理之旅<br>
                記錄每一天的進步
            </p>
            <div class="grid grid-cols-2 gap-12 text-center">
                <div class="bg-white bg-opacity-10 rounded-lg p-6 backdrop-blur-sm">
                    <div class="text-3xl font-bold mb-2">1000+</div>
                    <div class="text-sm opacity-90">活躍用戶</div>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-6 backdrop-blur-sm">
                    <div class="text-3xl font-bold mb-2">50K+</div>
                    <div class="text-sm opacity-90">記錄條數</div>
                </div>
            </div>
            
            <!-- 額外的功能特色 -->
            <div class="mt-12 text-center">
                <h3 class="text-lg font-semibold mb-4">為什麼選擇我們？</h3>
                <div class="space-y-3 text-sm opacity-90">
                    <div class="flex items-center justify-center">
                        <span class="mr-2">✓</span>
                        簡單易用的界面設計
                    </div>
                    <div class="flex items-center justify-center">
                        <span class="mr-2">✓</span>
                        安全的數據保護
                    </div>
                    <div class="flex items-center justify-center">
                        <span class="mr-2">✓</span>
                        跨設備同步
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 右側表單 -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-12 py-16 bg-gray-50">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-10">
                <a href="/" class="inline-block">
                    <div class="text-4xl mb-3">📊</div>
                    <div class="text-2xl font-bold text-gray-900">體重記錄</div>
                </a>
            </div>

            <!-- 表單卡片 -->
            <div class="bg-white rounded-2xl shadow-xl p-10">
                {{ $slot }}
            </div>

            <!-- 返回首頁連結 -->
            <div class="text-center mt-8">
                <a href="/" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-300">
                    ← 返回首頁
                </a>
            </div>
        </div>
    </div>
</div>
