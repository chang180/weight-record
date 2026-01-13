<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            {{ __('輸入體重記錄') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 新增記錄表單 -->
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <livewire:weight-record-form />

            <!-- 體重目標設定 -->
            @php
                $activeGoal = auth()->user()->activeWeightGoal;
            @endphp
            
            @if($activeGoal)
                <div class="mt-6 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-purple-800">🎯 當前目標</h3>
                        <a href="{{ route('goals.index') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">管理目標</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $activeGoal->target_weight }} 公斤</div>
                            <div class="text-sm text-purple-500">目標體重</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $activeGoal->target_date->format('Y/m/d') }}</div>
                            <div class="text-sm text-purple-500">目標日期</div>
                        </div>
                        <div class="text-center">
                            @php
                                $currentWeight = auth()->user()->weights()->latest('record_at')->first()?->weight ?? 0;
                                $startWeight = auth()->user()->start_weight ?? $currentWeight;
                                $targetWeight = $activeGoal->target_weight;
                                if ($startWeight && $currentWeight && $targetWeight) {
                                    $totalChange = abs($startWeight - $targetWeight);
                                    $currentChange = abs($startWeight - $currentWeight);
                                    $progress = $totalChange > 0 ? min(100, ($currentChange / $totalChange) * 100) : 0;
                                } else {
                                    $progress = 0;
                                }
                            @endphp
                            <div class="text-2xl font-bold text-purple-600">{{ round($progress) }}%</div>
                            <div class="text-sm text-purple-500">進度</div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full transition-all duration-500 ease-out" 
                                     style="width: {{ round($progress) }}%"></div>
                            </div>
                        </div>
                    </div>
                    @if($activeGoal->description)
                        <div class="mt-4 p-3 bg-white rounded-lg">
                            <p class="text-sm text-gray-600">{{ $activeGoal->description }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6 border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-indigo-800">🎯 設定體重目標</h3>
                            <p class="text-sm text-indigo-600 mt-1">設定目標可以幫助您更好地管理體重</p>
                        </div>
                        <a href="{{ route('goals.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition duration-300">
                            設定目標
                        </a>
                    </div>
                </div>
            @endif

            <!-- 遊戲化快速資訊 -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-4 text-white transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-xs font-semibold" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);">可用積分</p>
                            <p class="text-2xl font-bold text-white transition-all duration-300"
                               style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);"
                               x-data="{ points: {{ auth()->user()->available_points }} }"
                               x-text="points">{{ auth()->user()->available_points }}</p>
                        </div>
                        <div class="text-3xl transition-transform duration-300 hover:rotate-12">💎</div>
                    </div>
                    <a href="{{ route('daily-logs.index') }}" class="text-white text-xs hover:text-purple-100 mt-2 block transition-colors font-medium">
                        前往昨日任務 →
                    </a>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-4 text-white transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-xs font-semibold" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);">當前連續</p>
                            <p class="text-2xl font-bold text-white" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">{{ auth()->user()->current_streak }} 天</p>
                        </div>
                        <div class="text-3xl transition-transform duration-300 hover:scale-110">🔥</div>
                    </div>
                    <a href="{{ route('achievements.index') }}" class="text-white text-xs hover:text-orange-100 mt-2 block transition-colors font-medium">
                        查看成就 →
                    </a>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 text-white transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-xs font-semibold" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);">可兌換獎勵</p>
                            <p class="text-2xl font-bold text-white" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">5 種</p>
                        </div>
                        <div class="text-3xl transition-transform duration-300 hover:scale-110">🎁</div>
                    </div>
                    <a href="{{ route('rewards.index') }}" class="text-white text-xs hover:text-green-100 mt-2 block transition-colors font-medium">
                        前往商店 →
                    </a>
                </div>
            </div>

            <!-- 最近解鎖的成就 -->
            @php
                $recentAchievements = auth()->user()->achievements()
                    ->orderBy('user_achievements.unlocked_at', 'desc')
                    ->limit(3)
                    ->get();
            @endphp

            @if($recentAchievements->count() > 0)
                <div class="mt-6 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-4">🏆 最近解鎖的成就</h3>
                    <div class="space-y-3">
                        @foreach($recentAchievements as $achievement)
                            <div class="flex items-center bg-white rounded-lg p-3">
                                <div class="text-3xl mr-3">{{ $achievement->icon }}</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">{{ $achievement->name }}</h4>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($achievement->pivot->unlocked_at)->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 健康小提示 -->
            <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">健康小提示</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>每天同一時間測量體重，可以獲得更準確的變化趨勢。建議在早晨起床後、進食前測量。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
