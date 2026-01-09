<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
                🎯 目標詳情
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('goals.edit', $goal) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                    編輯
                </a>
                <a href="{{ route('goals.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                    返回列表
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        @if($goal->goal_type == 'lose')
                            📉 減重目標
                        @elseif($goal->goal_type == 'maintain')
                            ⚖️ 維持體重
                        @else
                            📈 增重目標
                        @endif
                    </h3>
                    @if($goal->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            活躍中
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- 目標信息 -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">目標信息</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">目標體重</span>
                                    <span class="font-semibold text-gray-900">{{ $goal->target_weight }} 公斤</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">目標日期</span>
                                    <span class="font-semibold text-gray-900">{{ $goal->target_date->format('Y年m月d日') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">創建時間</span>
                                    <span class="font-semibold text-gray-900">{{ $goal->created_at->format('Y年m月d日 H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">最後更新</span>
                                    <span class="font-semibold text-gray-900">{{ $goal->updated_at->format('Y年m月d日 H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($goal->description)
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">目標描述</h4>
                                <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $goal->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- 進度信息 -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">進度信息</h4>
                            <div class="space-y-4">
                                @php
                                    $daysLeft = now()->diffInDays($goal->target_date, false);
                                    $totalDays = (int) round($goal->created_at->diffInDays($goal->target_date));
                                    $daysPassed = (int) round($goal->created_at->diffInDays(now()));
                                    $progressPercentage = $totalDays > 0 ? min(100, ($daysPassed / $totalDays) * 100) : 0;
                                @endphp
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">剩餘天數</span>
                                    <span class="font-semibold {{ $daysLeft > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @if($daysLeft > 0)
                                            {{ $daysLeft }} 天
                                        @else
                                            已到期
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">總天數</span>
                                    <span class="font-semibold text-gray-900">{{ $totalDays }} 天</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">已過天數</span>
                                    <span class="font-semibold text-gray-900">{{ $daysPassed }} 天</span>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">時間進度</span>
                                        <span class="font-semibold text-gray-900">{{ round($progressPercentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 操作按鈕 -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between">
                        <div class="flex space-x-3">
                            @if(!$goal->is_active)
                                <form action="{{ route('goals.activate', $goal) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-300">
                                        設為活躍目標
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('goals.edit', $goal) }}" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                                編輯目標
                            </a>
                        </div>
                        
                        <form action="{{ route('goals.destroy', $goal) }}" method="POST" class="inline"
                              onsubmit="return confirm('確定要刪除這個目標嗎？此操作無法復原。')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300">
                                刪除目標
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
