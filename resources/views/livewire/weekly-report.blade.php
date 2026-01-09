<div>
    <!-- 週期資訊 -->
    <div class="mb-6 bg-white shadow-md rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    {{ $weekStart->format('Y年m月d日') }} - {{ $weekEnd->format('m月d日') }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">第 {{ $weekStart->week }} 週</p>
            </div>
            <div class="text-right flex gap-2">
                <button 
                    wire:click="previousWeek"
                    class="px-4 py-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition">
                    ← 上週
                </button>
                <button 
                    wire:click="nextWeek"
                    class="px-4 py-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition">
                    下週 →
                </button>
            </div>
        </div>
    </div>

    <!-- 體重變化 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="text-sm font-medium mb-1">週開始體重</div>
            <div class="text-3xl font-bold">
                {{ $startWeight ? number_format($startWeight, 1) . ' kg' : '未記錄' }}
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="text-sm font-medium mb-1">週結束體重</div>
            <div class="text-3xl font-bold">
                {{ $endWeight ? number_format($endWeight, 1) . ' kg' : '未記錄' }}
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="text-sm font-medium mb-1">本週減重</div>
            <div class="text-3xl font-bold">
                @if($startWeight && $endWeight)
                    {{ number_format($startWeight - $endWeight, 1) }} kg
                @else
                    未記錄
                @endif
            </div>
        </div>
    </div>

    <!-- 任務完成統計 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-indigo-600">
            <h3 class="text-lg font-bold text-white">✅ 任務完成統計</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $tasksCompleted['completed_days'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">完成天數</div>
                    <div class="text-xs text-gray-500 mt-1">共 {{ $tasksCompleted['total_days'] ?? 0 }} 天</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $tasksCompleted['workday_completed'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">工作日完成</div>
                    <div class="text-xs text-gray-500 mt-1">共 {{ $tasksCompleted['workday_total'] ?? 0 }} 天</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $tasksCompleted['weekend_completed'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">假日完成</div>
                    <div class="text-xs text-gray-500 mt-1">共 {{ $tasksCompleted['weekend_total'] ?? 0 }} 天</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 積分統計 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-yellow-400 to-orange-500">
            <h3 class="text-lg font-bold text-white">💰 積分統計</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $pointsEarned['daily_points'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">每日任務積分</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $pointsEarned['weekly_points'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">週任務積分</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $pointsEarned['total'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">總積分</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 解鎖成就 -->
    @if(count($achievementsUnlocked) > 0)
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500">
            <h3 class="text-lg font-bold text-white">🏆 本週解鎖成就</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($achievementsUnlocked as $achievement)
                    <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                        <div class="text-4xl mb-2">{{ $achievement['icon'] ?? '🎖️' }}</div>
                        <div class="text-sm font-medium text-gray-800">{{ $achievement['name'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- 連續達成 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-red-400 to-pink-500">
            <h3 class="text-lg font-bold text-white">🔥 連續達成</h3>
        </div>
        <div class="p-6">
            <div class="text-center">
                <div class="text-5xl font-bold text-red-600">{{ $streak }}</div>
                <div class="text-lg text-gray-600 mt-2">連續達成天數</div>
            </div>
        </div>
    </div>

    <!-- 下週目標 -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200">
        <h3 class="text-lg font-bold text-indigo-800 mb-3">🎯 下週目標建議</h3>
        <p class="text-indigo-700">{{ $nextWeekGoal }}</p>
    </div>
</div>
