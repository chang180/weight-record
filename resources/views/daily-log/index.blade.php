<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            📋 今日任務
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('achievement'))
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">🎉 恭喜！解鎖成就：{{ session('achievement') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 積分與連續達成 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white"
                     x-data="{ 
                         points: {{ auth()->user()->available_points }},
                         animate: false
                     }"
                     x-init="$watch('points', () => { animate = true; setTimeout(() => animate = false, 500); })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">可用積分</p>
                            <p class="text-3xl font-bold transition-all duration-300"
                               :class="animate ? 'scale-125' : ''"
                               x-text="points">{{ auth()->user()->available_points }}</p>
                        </div>
                        <div class="text-4xl transition-transform duration-300 hover:rotate-12">💎</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">當前連續</p>
                            <p class="text-3xl font-bold">{{ auth()->user()->current_streak }} 天</p>
                        </div>
                        <div class="text-4xl">🔥</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">最長連續</p>
                            <p class="text-3xl font-bold">{{ auth()->user()->longest_streak }} 天</p>
                        </div>
                        <div class="text-4xl">🏆</div>
                    </div>
                </div>
            </div>

            <!-- 今日任務清單 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-indigo-600">
                    <h3 class="text-lg font-bold text-white">
                        {{ $isWeekend ? '🎉 週末任務' : '💼 工作日任務' }}
                        <span class="text-indigo-200 text-sm ml-2">{{ now()->format('Y-m-d') }}</span>
                    </h3>
                    @php
                        $completedCount = collect($tasks)->filter(fn($t) => $t['completed'])->count();
                        $totalCount = count($tasks);
                        $progress = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                    @endphp
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-indigo-200 mb-1">
                            <span>進度</span>
                            <span>{{ $completedCount }} / {{ $totalCount }}</span>
                        </div>
                        <div class="w-full bg-indigo-300 rounded-full h-2">
                            <div class="bg-yellow-400 h-2 rounded-full transition-all duration-500 ease-out" 
                                 style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div x-data="{
                        tasks: @js($tasks),
                        dailyLogId: {{ $dailyLog?->id ?? 'null' }},
                        dailyPoints: {{ $dailyLog->daily_points ?? 0 }},
                        allCompleted: false,
                        checkAllCompleted() {
                            this.allCompleted = Object.values(this.tasks).every(t => t.completed);
                        },
                        toggleTask(taskKey) {
                            if (!this.dailyLogId) {
                                alert('請先建立今日記錄');
                                return;
                            }

                            fetch(`/daily-logs/${this.dailyLogId}/toggle-task`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ task: taskKey })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.tasks[taskKey].completed = data.dailyLog[taskKey];
                                    this.dailyPoints = data.dailyPoints;
                                    this.checkAllCompleted();
                                    
                                    if (data.unlockedAchievements && data.unlockedAchievements.length > 0) {
                                        const names = data.unlockedAchievements.map(a => a.name).join('、');
                                        const icons = data.unlockedAchievements.map(a => a.icon).join(' ');
                                        alert(icons + ' 恭喜！解鎖成就：' + names);
                                    }
                                    
                                    if (this.allCompleted) {
                                        setTimeout(() => {
                                            // 顯示完美一天的慶祝效果
                                        }, 300);
                                    }
                                }
                            });
                        },
                        init() {
                            this.checkAllCompleted();
                        }
                    }" class="space-y-3">
                        <template x-for="(task, key) in tasks" :key="key">
                            <div class="flex items-center p-4 border rounded-lg transition-all duration-300 cursor-pointer"
                                 :class="task.completed ? 'bg-green-50 border-green-300 shadow-md' : 'bg-white border-gray-200 hover:bg-gray-50'"
                                 @click="toggleTask(key)">
                                <div class="flex-shrink-0 mr-4 relative">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                                         :class="task.completed ? 'bg-green-500 border-green-500 scale-110' : 'border-gray-300'">
                                        <svg x-show="task.completed" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 scale-0"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="w-4 h-4 text-white" 
                                             fill="currentColor" 
                                             viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold transition-colors duration-300" 
                                       :class="task.completed ? 'text-green-700' : 'text-gray-800'"
                                       x-text="task.name"></p>
                                    <p class="text-sm text-gray-500" x-text="task.description"></p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <span class="text-2xl transition-transform duration-300"
                                          :class="task.completed ? 'scale-125' : ''"
                                          x-text="task.icon"></span>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="allCompleted" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 transform scale-90"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-4 p-4 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg text-white text-center">
                            <div class="text-2xl mb-2">✨ 完美一天！</div>
                            <div class="text-sm">所有任務都完成了！</div>
                        </div>
                    </div>

                    <!-- 體重記錄 -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="font-semibold text-gray-800 mb-4">📊 今日體重記錄</h4>
                        <form method="POST" action="{{ route('daily-logs.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">體重 (kg)</label>
                                <input id="weight" name="weight" type="number" step="0.1" min="0"
                                       value="{{ $dailyLog?->weight }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                       placeholder="例如：68.5" />
                            </div>
                            <div>
                                <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">備註</label>
                                <textarea id="note" name="note" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    placeholder="可選：記錄當天的飲食、運動或其他情況">{{ $dailyLog?->note }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300">
                                    {{ $dailyLog ? '更新記錄' : '建立記錄' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 今日積分統計 -->
                    @if($dailyLog)
                        <div class="mt-6 pt-6 border-t">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg transition-all duration-300 hover:shadow-md">
                                    <p class="text-sm text-blue-600 mb-1">每日任務積分</p>
                                    <p class="text-2xl font-bold text-blue-700 transition-all duration-300" 
                                       x-text="dailyPoints"
                                       x-data="{ 
                                           animate: false,
                                           init() {
                                               $watch('dailyPoints', () => {
                                                   this.animate = true;
                                                   setTimeout(() => this.animate = false, 500);
                                               });
                                           }
                                       }"
                                       :class="$data.animate ? 'scale-125 text-green-600' : ''">0</p>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <p class="text-sm text-green-600 mb-1">週任務積分</p>
                                    <p class="text-2xl font-bold text-green-700">{{ $dailyLog->weekly_points ?? 0 }}</p>
                                </div>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <p class="text-sm text-purple-600 mb-1">總積分</p>
                                    <p class="text-2xl font-bold text-purple-700">{{ auth()->user()->total_points }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 快捷連結 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('achievements.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="text-4xl mr-4">🏆</div>
                        <div>
                            <h4 class="font-semibold text-gray-800">成就牆</h4>
                            <p class="text-sm text-gray-500">查看已解鎖的成就</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('rewards.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="text-4xl mr-4">🎁</div>
                        <div>
                            <h4 class="font-semibold text-gray-800">獎勵商店</h4>
                            <p class="text-sm text-gray-500">用積分兌換獎勵</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
