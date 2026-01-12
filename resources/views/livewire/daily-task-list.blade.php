<div>
    <!-- 積分與連續達成 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl shadow-lg p-6 text-white"
             x-data="{
                 points: {{ $availablePoints }},
                 animate: false
             }"
             x-init="$watch('points', () => { animate = true; setTimeout(() => animate = false, 500); })"
             @points-updated.window="points = $event.detail.points; animate = true; setTimeout(() => animate = false, 500);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white text-base font-extrabold tracking-wider uppercase" style="text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8), 0 1px 3px rgba(0, 0, 0, 0.5);">可用積分</p>
                    <p class="text-3xl font-extrabold text-white transition-all duration-300 drop-shadow-lg"
                       style="text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6), 0 0 2px rgba(0, 0, 0, 0.3);"
                       :class="animate ? 'scale-125' : ''"
                       x-text="points">{{ $availablePoints }}</p>
                </div>
                <div class="text-4xl transition-transform duration-300 hover:rotate-12 drop-shadow-md">💎</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white text-base font-extrabold tracking-wider uppercase" style="text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8), 0 1px 3px rgba(0, 0, 0, 0.5);">當前連續</p>
                    <p class="text-3xl font-extrabold text-white drop-shadow-lg" style="text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6), 0 0 2px rgba(0, 0, 0, 0.3);">{{ $currentStreak }} 天</p>
                </div>
                <div class="text-4xl drop-shadow-md">🔥</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white text-base font-extrabold tracking-wider uppercase" style="text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8), 0 1px 3px rgba(0, 0, 0, 0.5);">最長連續</p>
                    <p class="text-3xl font-extrabold text-white drop-shadow-lg" style="text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6), 0 0 2px rgba(0, 0, 0, 0.3);">{{ $longestStreak }} 天</p>
                </div>
                <div class="text-4xl drop-shadow-md">🏆</div>
            </div>
        </div>
    </div>

    <!-- 今日任務清單 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6 border border-gray-200">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-indigo-600">
            <h3 class="text-lg font-bold text-white">
                {{ $isWeekend ? '🎉 週末任務' : '💼 工作日任務' }}
                <span class="text-indigo-100 text-sm ml-2">{{ now()->format('Y-m-d') }}</span>
            </h3>
            <div class="mt-2">
                <div class="flex justify-between text-xs text-indigo-100 mb-1">
                    <span>進度</span>
                    <span>{{ $this->completedCount }} / {{ $this->totalCount }}</span>
                </div>
                <div class="w-full bg-indigo-300/50 rounded-full h-2">
                    <div class="bg-yellow-400 h-2 rounded-full transition-all duration-500 ease-out shadow-sm"
                         style="width: {{ $this->progress }}%"></div>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white">
            @if(!$dailyLog)
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 text-2xl mr-3">⚠️</div>
                        <div class="flex-1">
                            <p class="text-yellow-800 font-semibold mb-1">請先建立今日記錄</p>
                            <p class="text-yellow-700 text-sm">
                                請先在下方的「今日體重記錄」區域建立記錄，然後就可以開始完成任務了！
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 text-2xl mr-3">💡</div>
                        <div class="flex-1">
                            <p class="text-blue-800 font-semibold mb-1">如何完成任務</p>
                            <p class="text-blue-700 text-sm">
                                點擊下方的任務項目即可切換完成狀態。完成的任務會變成綠色並顯示 ✓ 標記。
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-3">
                @foreach($tasks as $taskKey => $task)
                    <div
                        wire:click="toggleTask('{{ $taskKey }}')"
                        wire:loading.class="opacity-50 cursor-wait"
                        class="flex items-center p-4 border-2 rounded-lg transition-all duration-300 cursor-pointer group task-item
                            {{ $task['completed'] ? 'bg-green-50 border-green-400 shadow-md' : 'bg-white border-gray-300 hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-md' }}"
                        title="{{ $task['completed'] ? '點擊取消完成' : '點擊標記為完成' }}">
                        <div class="flex-shrink-0 mr-4 relative">
                            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300
                                {{ $task['completed'] ? 'bg-green-500 border-green-600 scale-110' : 'border-gray-300 group-hover:border-indigo-500' }}">
                                @if($task['completed'])
                                    <svg 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-0"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="w-5 h-5 text-white" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg
                                        class="w-4 h-4 text-gray-400 group-hover:text-indigo-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold transition-colors duration-300 mb-1
                                {{ $task['completed'] ? 'text-green-700' : 'text-gray-800 group-hover:text-indigo-700' }}">
                                {{ $task['name'] }}
                            </p>
                            <p class="text-sm transition-colors duration-300
                                {{ $task['completed'] ? 'text-green-600' : 'text-gray-600 group-hover:text-indigo-600' }}">
                                {{ $task['description'] }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <span class="text-3xl transition-transform duration-300
                                {{ $task['completed'] ? 'scale-125' : 'group-hover:scale-110' }}">
                                {{ $task['icon'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
                
                @if($this->allCompleted)
                    <div 
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 transform scale-90"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        class="mt-4 p-4 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg text-white text-center">
                        <div class="text-2xl mb-2">✨ 完美一天！</div>
                        <div class="text-sm">所有任務都完成了！</div>
                    </div>
                @endif
            </div>

            <!-- 體重記錄表單 -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-4">📊 今日體重記錄</h4>
                <form wire:submit="storeWeightRecord" class="space-y-4">
                    <div>
                        <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">體重 (kg)</label>
                        <input
                            id="weight"
                            type="number"
                            step="0.1"
                            min="0"
                            wire:model="weight"
                            value="{{ $dailyLog?->weight }}"
                            class="w-full px-4 py-2 bg-white border rounded-lg text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('weight') border-red-500 @else border-gray-300 @enderror"
                            placeholder="例如：68.5" />
                        @error('weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">備註</label>
                        <textarea
                            id="notes"
                            rows="2"
                            wire:model="notes"
                            class="w-full px-4 py-2 bg-white border rounded-lg text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('notes') border-red-500 @else border-gray-300 @enderror"
                            placeholder="可選：記錄當天的飲食、運動或其他情況">{{ $dailyLog?->notes }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="storeWeightRecord"
                            class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="storeWeightRecord">
                                {{ $dailyLog ? '更新記錄' : '建立記錄' }}
                            </span>
                            <span wire:loading wire:target="storeWeightRecord" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                處理中...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 今日積分統計 -->
            @if($dailyLog)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 border border-blue-200 rounded-lg transition-all duration-300 hover:shadow-md">
                            <p class="text-sm text-blue-700 mb-1">每日任務積分</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $dailyPoints }}</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 border border-green-200 rounded-lg transition-all duration-300 hover:shadow-md">
                            <p class="text-sm text-green-700 mb-1">週任務積分</p>
                            <p class="text-2xl font-bold text-green-600">{{ $dailyLog->weekly_points ?? 0 }}</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 border border-purple-200 rounded-lg transition-all duration-300 hover:shadow-md">
                            <p class="text-sm text-purple-700 mb-1">總積分</p>
                            <p class="text-2xl font-bold text-purple-600">{{ auth()->user()->total_points }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 成就解鎖通知 -->
    @if($showAchievementNotification && count($unlockedAchievements) > 0)
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 transform translate-y-[-100px] scale-90"
            x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.away="setTimeout(() => { show = false; $wire.showAchievementNotification = false; }, 5000)"
            class="fixed top-4 right-4 z-50 bg-gradient-to-r from-yellow-400 to-orange-500 border-2 border-yellow-300 rounded-lg p-4 shadow-lg animate-pulse">
            <div class="flex items-center">
                <div class="flex-shrink-0 text-4xl animate-bounce">🎉</div>
                <div class="ml-4 flex-1">
                    <p class="text-lg font-bold text-white">成就解鎖！</p>
                    <p class="text-sm text-yellow-100">
                        {{ implode('、', array_map(fn($a) => $a->name, $unlockedAchievements)) }}
                    </p>
                </div>
                <button @click="show = false; $wire.showAchievementNotification = false" class="text-white hover:text-yellow-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- 快捷連結 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('achievements.index') }}" class="bg-white border border-gray-200 rounded-lg shadow-md p-6 hover:shadow-lg hover:border-indigo-300 hover:bg-indigo-50 transition duration-300">
            <div class="flex items-center">
                <div class="text-4xl mr-4">🏆</div>
                <div>
                    <h4 class="font-semibold text-gray-800">成就牆</h4>
                    <p class="text-sm text-gray-600">查看已解鎖的成就</p>
                </div>
            </div>
        </a>
        <a href="{{ route('rewards.index') }}" class="bg-white border border-gray-200 rounded-lg shadow-md p-6 hover:shadow-lg hover:border-indigo-300 hover:bg-indigo-50 transition duration-300">
            <div class="flex items-center">
                <div class="text-4xl mr-4">🎁</div>
                <div>
                    <h4 class="font-semibold text-gray-800">獎勵商店</h4>
                    <p class="text-sm text-gray-600">用積分兌換獎勵</p>
                </div>
            </div>
        </a>
    </div>
</div>
