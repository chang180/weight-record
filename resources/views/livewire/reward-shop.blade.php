<div>
    <!-- 可用積分 -->
    <div class="mb-8 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-8 text-white text-center"
         x-data="{ 
             points: {{ $availablePoints }},
             animate: false 
         }"
         x-init="$watch('points', () => { animate = true; setTimeout(() => animate = false, 500); })"
         @points-updated.window="points = $event.detail.points; animate = true; setTimeout(() => animate = false, 500);">
        <p class="text-purple-100 mb-2">您的可用積分</p>
        <p class="text-5xl font-bold mb-2 transition-all duration-300"
           :class="animate ? 'scale-125' : ''"
           x-text="points">{{ $availablePoints }}</p>
        <p class="text-purple-100 text-sm">完成更多任務來賺取積分！</p>
    </div>

    <!-- 獎勵列表 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($rewards as $index => $reward)
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="text-5xl mb-2">🎁</div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $reward['name'] }}</h3>
                    </div>

                    <p class="text-gray-600 text-sm mb-6 text-center min-h-[3rem]">{{ $reward['description'] }}</p>

                    <div class="flex items-center justify-center mb-4">
                        <span class="text-3xl font-bold text-purple-600">{{ $reward['points'] }}</span>
                        <span class="text-gray-500 ml-2">積分</span>
                    </div>

                    @if($availablePoints >= $reward['points'])
                        <button 
                            wire:click="selectReward({{ $index }})"
                            class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition duration-300">
                            立即兌換
                        </button>
                    @else
                        <button 
                            type="button" 
                            disabled
                            class="w-full bg-gray-300 text-gray-500 py-3 rounded-lg font-bold cursor-not-allowed">
                            積分不足
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- 兌換確認 Modal -->
    @if($showRedeemModal && $selectedReward)
        <div 
            x-data="{ show: @entangle('showRedeemModal') }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-show="show"
        >
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                <div class="text-3xl">🎁</div>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">確認兌換</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        確定要兌換 <strong>{{ $selectedReward['name'] }}</strong> 嗎？
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        將消耗 <strong class="text-purple-600">{{ $selectedReward['points'] }}</strong> 積分
                                    </p>
                                    <div class="mt-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">備註（選填）</label>
                                        <textarea 
                                            id="notes"
                                            wire:model="notes"
                                            rows="3"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-400 focus:outline-none @error('notes') border-red-500 @else border-gray-300 @enderror"
                                            placeholder="記錄這次兌換的原因或計劃..."></textarea>
                                        @error('notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            wire:click="redeem"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="redeem">確認兌換</span>
                            <span wire:loading wire:target="redeem" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                處理中...
                            </span>
                        </button>
                        <button 
                            wire:click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            取消
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 成功通知 -->
    <div 
        x-data="{ show: false }"
        x-show="show"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform translate-y-[-100px]"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @reward-redeemed.window="show = true; setTimeout(() => show = false, 5000)"
        class="fixed top-4 right-4 z-50 bg-green-50 border-2 border-green-300 rounded-lg p-4 shadow-lg"
        style="display: none;">
        <div class="flex items-center">
            <div class="flex-shrink-0 text-2xl">🎉</div>
            <div class="ml-4">
                <p class="text-sm font-medium text-green-800">獎勵兌換成功！</p>
            </div>
        </div>
    </div>

    <!-- 溫馨提醒 -->
    <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">溫馨提醒</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>獎勵是為了激勵自己，但請記得不要過度放縱。適度享受獎勵，繼續保持健康的生活習慣！</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 如何賺取積分 -->
    <div class="mt-6 bg-green-50 rounded-xl p-6 border border-green-200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">如何賺取積分</h3>
                <div class="mt-2 text-sm text-green-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>每日完成任務可獲得 50 積分</li>
                        <li>週任務全勤最高可獲得 350 積分</li>
                        <li>解鎖特殊成就可獲得額外積分獎勵</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
