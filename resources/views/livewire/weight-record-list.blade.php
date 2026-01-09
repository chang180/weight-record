<div>
    <!-- 日期範圍篩選 -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-indigo-600">
            <h3 class="text-lg font-bold text-white">篩選記錄</h3>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('開始日期') }}</label>
                    <input 
                        id="start_date" 
                        type="date" 
                        wire:model.live="start_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('結束日期') }}</label>
                    <input 
                        id="end_date" 
                        type="date" 
                        wire:model.live="end_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                </div>
                <div class="flex space-x-2">
                    <button 
                        type="button"
                        wire:click="resetFilters"
                        class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg shadow hover:bg-gray-300 transition duration-300">
                        {{ __('重置') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="px-6 py-4 bg-indigo-600 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">所有體重記錄</h3>
            <div class="flex space-x-2">
                <a href="{{ route('weights.export.csv') }}" class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg shadow hover:bg-green-700 transition duration-300">
                    📊 CSV
                </a>
                <a href="{{ route('weights.export.pdf') }}" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg shadow hover:bg-red-700 transition duration-300">
                    📄 PDF
                </a>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-white text-indigo-600 font-bold rounded-lg shadow hover:bg-gray-100 transition duration-300">
                    {{ __('新增記錄') }}
                </a>
            </div>
        </div>
        <div class="p-6">
            @if ($weights->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">體重 (kg)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備註</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($weights as $item)
                            <tr class="hover:bg-gray-50">
                                @if($editingId === $item->id)
                                    <!-- 編輯模式 -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            type="date" 
                                            wire:model="editingRecordAt"
                                            class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm @error('editingRecordAt') border-red-500 @else border-gray-300 @enderror" />
                                        @error('editingRecordAt')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            type="number" 
                                            step="0.1" 
                                            min="0" 
                                            wire:model="editingWeight"
                                            class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm w-24 @error('editingWeight') border-red-500 @else border-gray-300 @enderror" />
                                        @error('editingWeight')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4">
                                        <input 
                                            type="text" 
                                            wire:model="editingNote"
                                            placeholder="無備註"
                                            class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm w-full @error('editingNote') border-red-500 @else border-gray-300 @enderror" />
                                        @error('editingNote')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button 
                                            wire:click="update"
                                            wire:loading.attr="disabled"
                                            wire:target="update"
                                            class="text-green-600 hover:text-green-900 font-medium mr-2 inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="update">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                儲存
                                            </span>
                                            <span wire:loading wire:target="update" class="inline-flex items-center">
                                                <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                處理中...
                                            </span>
                                        </button>
                                        <button 
                                            wire:click="cancelEdit"
                                            class="text-gray-600 hover:text-gray-900 font-medium inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            取消
                                        </button>
                                    </td>
                                @else
                                    <!-- 顯示模式 -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $item->record_at->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->weight }} kg
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $item->note ?? '無備註' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button 
                                            wire:click="edit({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="edit({{ $item->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium mr-2 inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="edit({{ $item->id }})">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                編輯
                                            </span>
                                            <span wire:loading wire:target="edit({{ $item->id }})" class="inline-flex items-center">
                                                <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                處理中...
                                            </span>
                                        </button>
                                        <button 
                                            wire:click="confirmDelete({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="confirmDelete({{ $item->id }})"
                                            class="text-red-600 hover:text-red-900 font-medium inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="confirmDelete({{ $item->id }})">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                刪除
                                            </span>
                                            <span wire:loading wire:target="confirmDelete({{ $item->id }})" class="inline-flex items-center">
                                                <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                處理中...
                                            </span>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $weights->links() }}
                </div>
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">目前還沒有記錄</h3>
                    <p class="mt-1 text-sm text-gray-500">開始記錄您的體重變化吧！</p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            新增第一筆記錄
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 刪除確認 Modal -->
    @if($showDeleteModal)
        <div 
            x-data="{ show: @entangle('showDeleteModal') }"
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
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">確認刪除</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">確定要刪除這筆體重記錄嗎？此操作無法復原。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            wire:click="delete"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="delete">刪除</span>
                            <span wire:loading wire:target="delete">處理中...</span>
                        </button>
                        <button 
                            wire:click="cancelDelete"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            取消
                        </button>
                    </div>
                </div>
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
                <h3 class="text-sm font-medium text-blue-800">體重管理小提示</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>定期記錄體重可以幫助您更好地了解自己的健康狀況，並及時調整生活習慣。建議每週至少記錄2-3次。</p>
                </div>
            </div>
        </div>
    </div>
</div>
