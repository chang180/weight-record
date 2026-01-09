<div>
    <!-- 成就統計 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">已解鎖成就</p>
                    <p class="text-3xl font-bold">{{ count($unlockedIds) }} / {{ count($achievements) }}</p>
                </div>
                <div class="text-4xl">🌟</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">完成進度</p>
                    <p class="text-3xl font-bold">
                        {{ count($achievements) > 0 ? round((count($unlockedIds) / count($achievements)) * 100) : 0 }}%
                    </p>
                </div>
                <div class="text-4xl">📈</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">可用積分</p>
                    <p class="text-3xl font-bold">{{ auth()->user()->available_points }}</p>
                </div>
                <div class="text-4xl">💎</div>
            </div>
        </div>
    </div>

    @if(isset($achievementGroups['weight_milestone']))
        <!-- 體重里程碑成就 -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">📉 體重里程碑</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($achievementGroups['weight_milestone'] as $achievement)
                    @php
                        $isUnlocked = $achievement->is_unlocked ?? false;
                        $userAchievement = $isUnlocked ? $achievement->user_achievement?->pivot : null;
                    @endphp
                    <a href="{{ route('achievements.show', $achievement->code) }}" class="block">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg {{ $isUnlocked ? 'ring-2 ring-yellow-400' : 'opacity-60' }}">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-4xl">{{ $achievement->icon }}</div>
                                    @if($isUnlocked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            已解鎖
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            未解鎖
                                        </span>
                                    @endif
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">{{ $achievement->name }}</h4>
                                <p class="text-sm text-gray-600 mb-3">{{ $achievement->description }}</p>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">目標：{{ $achievement->requirement_value }} kg</span>
                                    @if($isUnlocked && $userAchievement)
                                        <span class="text-green-600 font-medium">
                                            {{ \Carbon\Carbon::parse($userAchievement->unlocked_at)->format('Y-m-d') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($achievementGroups['special']))
        <!-- 特殊成就 -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">⭐ 特殊成就</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($achievementGroups['special'] as $achievement)
                    @php
                        $isUnlocked = in_array($achievement->id, $unlockedIds);
                        $userAchievement = $isUnlocked ? $achievement->users->first()?->pivot : null;
                    @endphp
                    <a href="{{ route('achievements.show', $achievement->id) }}" class="block">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg {{ $isUnlocked ? 'ring-2 ring-yellow-400 transform hover:scale-105' : 'opacity-60' }}"
                             @if($isUnlocked)
                             x-data="{ 
                                 animate: false,
                                 init() {
                                     this.animate = true;
                                     setTimeout(() => this.animate = false, 1000);
                                 }
                             }"
                             :class="animate ? 'animate-pulse' : ''"
                             @endif>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-4xl transition-transform duration-300 {{ $isUnlocked ? 'hover:rotate-12' : '' }}">{{ $achievement->icon }}</div>
                                    @if($isUnlocked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            已解鎖
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            未解鎖
                                        </span>
                                    @endif
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">{{ $achievement->name }}</h4>
                                <p class="text-sm text-gray-600 mb-3">{{ $achievement->description }}</p>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-purple-600 font-medium">+{{ $achievement->points_reward }} 積分</span>
                                    @if($isUnlocked && $userAchievement)
                                        <span class="text-green-600 font-medium">
                                            {{ \Carbon\Carbon::parse($userAchievement->unlocked_at)->format('Y-m-d') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($achievements) == 0)
        <!-- 無成就提示 -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <div class="text-6xl mb-4">🏆</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">還沒有成就資料</h3>
            <p class="text-gray-600">請執行 Seeder 來建立成就資料</p>
        </div>
    @endif
</div>
