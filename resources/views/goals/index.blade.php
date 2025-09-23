<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
                🎯 我的體重目標
            </h2>
            <a href="{{ route('goals.create') }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
                設定新目標
            </a>
        </div>
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

            @if($goals->count() > 0)
                <div class="grid gap-6">
                    @foreach($goals as $goal)
                        <div class="bg-white rounded-lg shadow-md p-6 {{ $goal->is_active ? 'ring-2 ring-indigo-500' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            @if($goal->goal_type == 'lose')
                                                📉 減重目標
                                            @elseif($goal->goal_type == 'maintain')
                                                ⚖️ 維持體重
                                            @else
                                                📈 增重目標
                                            @endif
                                        </h3>
                                        @if($goal->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                活躍中
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">目標體重</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ $goal->target_weight }} 公斤</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">目標日期</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ $goal->target_date->format('Y-m-d') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">進度</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                @php
                                                    $daysLeft = now()->diffInDays($goal->target_date, false);
                                                    if ($daysLeft > 0) {
                                                        echo $daysLeft . ' 天';
                                                    } else {
                                                        echo '已到期';
                                                    }
                                                @endphp
                                            </p>
                                        </div>
                                    </div>

                                    @if($goal->description)
                                        <p class="text-gray-600 mb-4">{{ $goal->description }}</p>
                                    @endif

                                    <div class="text-sm text-gray-500">
                                        創建於 {{ $goal->created_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>

                                <div class="flex space-x-2 ml-4">
                                    @if(!$goal->is_active)
                                        <form action="{{ route('goals.activate', $goal) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition duration-300">
                                                啟用
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('goals.edit', $goal) }}" 
                                       class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition duration-300">
                                        編輯
                                    </a>
                                    
                                    <form action="{{ route('goals.destroy', $goal) }}" method="POST" class="inline"
                                          onsubmit="return confirm('確定要刪除這個目標嗎？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition duration-300">
                                            刪除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 分頁 -->
                <div class="mt-8">
                    {{ $goals->links() }}
                </div>
            @else
                <!-- 無目標提示 -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">還沒有設定體重目標</h3>
                    <p class="text-gray-600 mb-6">設定一個目標來幫助您更好地管理體重</p>
                    <a href="{{ route('goals.create') }}" 
                       class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">
                        設定第一個目標
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
