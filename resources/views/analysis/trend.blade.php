<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            📊 體重趨勢分析
        </h2>
    </x-slot>
            <!-- 分析期間選擇 -->
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">分析期間</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('analysis.trend', ['days' => 7]) }}" 
                           class="px-4 py-2 rounded-lg {{ $days == 7 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-300">
                            最近7天
                        </a>
                        <a href="{{ route('analysis.trend', ['days' => 30]) }}" 
                           class="px-4 py-2 rounded-lg {{ $days == 30 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-300">
                            最近30天
                        </a>
                        <a href="{{ route('analysis.trend', ['days' => 90]) }}" 
                           class="px-4 py-2 rounded-lg {{ $days == 90 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-300">
                            最近90天
                        </a>
                        <a href="{{ route('analysis.trend', ['days' => 365]) }}" 
                           class="px-4 py-2 rounded-lg {{ $days == 365 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-300">
                            最近一年
                        </a>
                    </div>
                </div>
            </div>

            @if($weights->count() > 0)
                <!-- 關鍵指標卡片 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span class="text-blue-600 text-lg">📈</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">體重變化</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    @if($analysis['weight_change'] > 0)
                                        +{{ $analysis['weight_change'] }} 公斤
                                    @elseif($analysis['weight_change'] < 0)
                                        {{ $analysis['weight_change'] }} 公斤
                                    @else
                                        無變化
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <span class="text-green-600 text-lg">⚖️</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">平均體重</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analysis['average_weight'] }} 公斤</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <span class="text-purple-600 text-lg">📊</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">記錄次數</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analysis['total_records'] }} 次</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <span class="text-orange-600 text-lg">🎯</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">一致性分數</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analysis['consistency_score'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 趨勢分析 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- 趨勢方向 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 趨勢方向</h3>
                        <div class="text-center">
                            @if($analysis['trend_direction'] == 'increasing')
                                <div class="text-6xl mb-4">📈</div>
                                <p class="text-xl font-bold text-red-600">上升趨勢</p>
                                <p class="text-gray-600">體重呈現上升趨勢</p>
                            @elseif($analysis['trend_direction'] == 'decreasing')
                                <div class="text-6xl mb-4">📉</div>
                                <p class="text-xl font-bold text-green-600">下降趨勢</p>
                                <p class="text-gray-600">體重呈現下降趨勢</p>
                            @else
                                <div class="text-6xl mb-4">➡️</div>
                                <p class="text-xl font-bold text-blue-600">穩定趨勢</p>
                                <p class="text-gray-600">體重保持穩定</p>
                            @endif
                        </div>
                    </div>

                    <!-- 變化統計 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 變化統計</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">週變化</span>
                                <span class="font-semibold {{ $analysis['weekly_change'] > 0 ? 'text-red-600' : ($analysis['weekly_change'] < 0 ? 'text-green-600' : 'text-gray-600') }}">
                                    @if($analysis['weekly_change'] > 0)
                                        +{{ $analysis['weekly_change'] }} 公斤
                                    @elseif($analysis['weekly_change'] < 0)
                                        {{ $analysis['weekly_change'] }} 公斤
                                    @else
                                        無變化
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">月變化</span>
                                <span class="font-semibold {{ $analysis['monthly_change'] > 0 ? 'text-red-600' : ($analysis['monthly_change'] < 0 ? 'text-green-600' : 'text-gray-600') }}">
                                    @if($analysis['monthly_change'] > 0)
                                        +{{ $analysis['monthly_change'] }} 公斤
                                    @elseif($analysis['monthly_change'] < 0)
                                        {{ $analysis['monthly_change'] }} 公斤
                                    @else
                                        無變化
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">波動性</span>
                                <span class="font-semibold text-gray-900">{{ $analysis['volatility'] }} 公斤</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 體重記錄表格 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-indigo-600">
                        <h3 class="text-lg font-bold text-white">📋 體重記錄詳情</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">體重</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">變化</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備註</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($weights as $index => $weight)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $weight->record_at->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $weight->weight }} 公斤
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($index > 0)
                                                @php
                                                    $previousWeight = $weights[$index - 1]->weight;
                                                    $change = $weight->weight - $previousWeight;
                                                @endphp
                                                @if($change > 0)
                                                    <span class="text-red-600">+{{ $change }}</span>
                                                @elseif($change < 0)
                                                    <span class="text-green-600">{{ $change }}</span>
                                                @else
                                                    <span class="text-gray-500">0</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $weight->note ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- 無數據提示 -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">📊</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">暫無分析數據</h3>
                    <p class="text-gray-600 mb-6">您還沒有記錄足夠的體重數據進行趨勢分析</p>
                    <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">
                        開始記錄體重
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
