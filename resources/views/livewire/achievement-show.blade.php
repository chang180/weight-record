<div>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- 成就圖示與狀態 -->
        <div class="bg-gradient-to-r {{ $isUnlocked ? 'from-yellow-400 to-yellow-500' : 'from-gray-400 to-gray-500' }} p-8 text-center">
            <div class="text-8xl mb-4">{{ $achievement->icon }}</div>
            <h3 class="text-3xl font-bold text-white mb-2">{{ $achievement->name }}</h3>
            @if($isUnlocked)
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white">
                    ✓ 已解鎖
                </span>
            @else
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white">
                    🔒 未解鎖
                </span>
            @endif
        </div>

        <!-- 成就詳情 -->
        <div class="p-8">
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-500 mb-2">描述</h4>
                <p class="text-lg text-gray-800">{{ $achievement->description }}</p>
            </div>

            @if($achievement->type === 'weight_milestone')
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-500 mb-2">達成條件</h4>
                    <p class="text-lg text-gray-800">體重達到 {{ $achievement->requirement_value }} kg</p>
                </div>
            @endif

            @if($achievement->points_reward > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-500 mb-2">獎勵積分</h4>
                    <p class="text-lg text-purple-600 font-bold">+{{ $achievement->points_reward }} 積分</p>
                </div>
            @endif

            @if($isUnlocked && $userAchievement)
                <div class="border-t pt-6">
                    <h4 class="text-sm font-semibold text-gray-500 mb-4">解鎖資訊</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-600 mb-1">解鎖時間</p>
                            <p class="text-lg font-semibold text-green-700">
                                {{ \Carbon\Carbon::parse($userAchievement->unlocked_at)->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        @if($userAchievement->weight_at_unlock)
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 mb-1">當時體重</p>
                                <p class="text-lg font-semibold text-blue-700">{{ $userAchievement->weight_at_unlock }} kg</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="border-t pt-6">
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-600 mb-4">繼續努力，即將解鎖此成就！</p>
                        <a href="{{ route('daily-logs.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                            前往今日任務
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 返回按鈕 -->
    <div class="mt-6 text-center">
        <a href="{{ route('achievements.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            返回成就牆
        </a>
    </div>
</div>
