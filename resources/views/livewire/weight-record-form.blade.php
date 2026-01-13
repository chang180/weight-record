<div>
    <!-- 通知訊息 -->
    @if($recordingReward)
        <div x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform translate-y-[-100px] scale-90"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="setTimeout(() => show = false, 5000)"
             class="mb-4 bg-gradient-to-r from-green-400 to-green-500 border-2 border-green-300 rounded-lg p-4 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 text-4xl">✅</div>
                <div class="ml-4 flex-1">
                    <p class="text-lg font-bold text-white">記錄體重獲得 +{{ $recordingReward }} 積分</p>
                </div>
                <button @click="show = false" class="text-white hover:text-green-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if($pointsDeducted && $deductionReason)
        <div x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform translate-x-[-100px] scale-90"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="setTimeout(() => show = false, 5000)"
             class="mb-4 bg-gradient-to-r from-orange-500 to-red-500 border-2 border-orange-300 rounded-lg p-4 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 text-4xl">⚠️</div>
                <div class="ml-4 flex-1">
                    <p class="text-lg font-bold text-white">積分扣除</p>
                    <p class="text-sm text-orange-100">
                        因{{ $deductionReason }}，扣除 {{ $pointsDeducted }} 積分
                        @if($pointsToDeduct && $pointsDeducted < $pointsToDeduct)
                            （應扣 {{ $pointsToDeduct }} 積分，因積分不足僅扣除 {{ $pointsDeducted }} 積分，尚欠 {{ $pointsDebt }} 積分）
                        @elseif($pointsToDeduct)
                            （應扣 {{ $pointsToDeduct }} 積分）
                        @endif
                        @if($recordingReward)
                            （記錄獎勵 +{{ $recordingReward }} 積分）
                        @endif
                    </p>
                </div>
                <button @click="show = false" class="text-white hover:text-orange-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(count($unlockedAchievements) > 0)
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform translate-y-[-100px] scale-90"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="setTimeout(() => show = false, 5000)"
             class="mb-4 bg-gradient-to-r from-yellow-400 to-orange-500 border-2 border-yellow-300 rounded-lg p-4 shadow-lg animate-pulse">
            <div class="flex items-center">
                <div class="flex-shrink-0 text-4xl animate-bounce">🎉</div>
                <div class="ml-4 flex-1">
                    <p class="text-lg font-bold text-white">成就解鎖！</p>
                    <p class="text-sm text-yellow-100">
                        {{ implode('、', array_map(fn($a) => $a->name, $unlockedAchievements)) }}
                    </p>
                </div>
                <button @click="show = false" class="text-white hover:text-yellow-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if($showTaskReminder)
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform translate-y-[-100px] scale-90"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="setTimeout(() => show = false, 8000)"
             class="mb-4 bg-gradient-to-r from-blue-500 to-indigo-600 border-2 border-blue-300 rounded-lg p-4 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 text-4xl">✅</div>
                <div class="ml-4 flex-1">
                    <p class="text-lg font-bold text-white">記錄成功！</p>
                    <p class="text-sm text-blue-100 mb-2">
                        別忘了勾選昨天完成的任務，獲取更多積分！
                    </p>
                    <a href="{{ route('daily-logs.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 font-semibold text-sm rounded-lg hover:bg-blue-50 transition duration-300">
                        前往任務頁面 →
                    </a>
                </div>
                <button @click="show = false; $wire.showTaskReminder = false" class="text-white hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- 表單 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="px-6 py-4 bg-indigo-600">
            <h3 class="text-lg font-bold text-white">新增體重記錄</h3>
        </div>
        <div class="p-6">
            <form wire:submit="store" class="space-y-6">
                <div>
                    <label for="record_at" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('記錄日期') }}</label>
                    <input 
                        id="record_at" 
                        type="date" 
                        wire:model="record_at"
                        required 
                        autofocus
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none @error('record_at') border-red-500 @else border-gray-300 @enderror" />
                    @error('record_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('體重 (kg)') }}</label>
                    <input 
                        id="weight" 
                        type="number" 
                        step="0.1" 
                        min="0" 
                        wire:model="weight"
                        required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none @error('weight') border-red-500 @else border-gray-300 @enderror"
                        placeholder="例如：68.5" />
                    @error('weight')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('備註') }}</label>
                    <textarea 
                        id="note" 
                        rows="2"
                        wire:model="note"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none @error('note') border-red-500 @else border-gray-300 @enderror"
                        placeholder="可選：記錄當天的飲食、運動或其他情況"></textarea>
                    @error('note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end">
                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="store"
                        class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300 transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="store">{{ __('送出記錄') }}</span>
                        <span wire:loading wire:target="store" class="flex items-center">
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
    </div>
</div>
