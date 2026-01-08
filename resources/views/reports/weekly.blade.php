<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            📅 週報表
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 週期資訊 -->
            <div class="mb-6 bg-white shadow-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $week_start->format('Y年m月d日') }} - {{ $week_end->format('m月d日') }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">第 {{ $week_start->week }} 週</p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('reports.weekly', ['weekStart' => $week_start->copy()->subWeek()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 text-indigo-600 hover:text-indigo-800">← 上週</a>
                        <a href="{{ route('reports.weekly', ['weekStart' => $week_start->copy()->addWeek()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 text-indigo-600 hover:text-indigo-800">下週 →</a>
                    </div>
                </div>
            </div>

            <!-- 體重變化 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium mb-1">週開始體重</div>
                    <div class="text-3xl font-bold">
                        {{ $start_weight ? number_format($start_weight, 1) . ' kg' : '未記錄' }}
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium mb-1">週結束體重</div>
                    <div class="text-3xl font-bold">
                        {{ $end_weight ? number_format($end_weight, 1) . ' kg' : '未記錄' }}
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium mb-1">本週減重</div>
                    <div class="text-3xl font-bold">
                        @if($start_weight && $end_weight)
                            {{ number_format($start_weight - $end_weight, 1) }} kg
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
                            <div class="text-3xl font-bold text-indigo-600">{{ $tasks_completed['completed_days'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">完成天數</div>
                            <div class="text-xs text-gray-500 mt-1">共 {{ $tasks_completed['total_days'] }} 天</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $tasks_completed['workday_completed'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">工作日完成</div>
                            <div class="text-xs text-gray-500 mt-1">共 {{ $tasks_completed['workday_total'] }} 天</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-600">{{ $tasks_completed['weekend_completed'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">假日完成</div>
                            <div class="text-xs text-gray-500 mt-1">共 {{ $tasks_completed['weekend_total'] }} 天</div>
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
                            <div class="text-3xl font-bold text-yellow-600">{{ $points_earned['daily_points'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">每日任務積分</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">{{ $points_earned['weekly_points'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">週任務積分</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ $points_earned['total'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">總積分</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 解鎖成就 -->
            @if(count($achievements_unlocked) > 0)
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500">
                    <h3 class="text-lg font-bold text-white">🏆 本週解鎖成就</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($achievements_unlocked as $achievement)
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
                <p class="text-indigo-700">{{ $next_week_goal }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
