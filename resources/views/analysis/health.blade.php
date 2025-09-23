@extends('layouts.app')

@section('title', '健康指標分析')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-indigo-700 mb-2">
            🏥 健康指標分析
        </h1>
        <p class="text-gray-600">了解您的健康指標和建議</p>
    </div>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!isset($hasData) || $hasData)
                <!-- BMI 指標卡片 -->
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <div class="text-center">
                        <div class="text-6xl mb-4">{{ $metrics['bmi_category']['icon'] }}</div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-2">BMI: {{ $metrics['bmi'] }}</h3>
                        <p class="text-xl font-semibold mb-2 
                            @if($metrics['bmi_category']['color'] == 'green') text-green-600
                            @elseif($metrics['bmi_category']['color'] == 'yellow') text-yellow-600
                            @elseif($metrics['bmi_category']['color'] == 'red') text-red-600
                            @else text-blue-600
                            @endif">
                            {{ $metrics['bmi_category']['name'] }}
                        </p>
                        <p class="text-gray-600">{{ $metrics['bmi_category']['description'] }}</p>
                    </div>
                </div>

                <!-- 基本資訊 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <div class="text-4xl mb-2">⚖️</div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">當前體重</h4>
                        <p class="text-2xl font-bold text-indigo-600">{{ $metrics['weight'] }} 公斤</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <div class="text-4xl mb-2">📏</div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">身高</h4>
                        <p class="text-2xl font-bold text-indigo-600">{{ $metrics['height'] }} 公分</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <div class="text-4xl mb-2">🎯</div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">理想體重範圍</h4>
                        <p class="text-lg font-bold text-indigo-600">
                            {{ $metrics['ideal_weight_min'] }} - {{ $metrics['ideal_weight_max'] }} 公斤
                        </p>
                    </div>
                </div>

                <!-- 健康建議 -->
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">💡 健康建議</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-4xl mb-4">🍎</div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">飲食建議</h4>
                            <p class="text-gray-600 leading-relaxed">{{ $metrics['health_advice']['飲食建議'] }}</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-4xl mb-4">🏃‍♂️</div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">運動建議</h4>
                            <p class="text-gray-600 leading-relaxed">{{ $metrics['health_advice']['運動建議'] }}</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-4xl mb-4">🌙</div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">生活建議</h4>
                            <p class="text-gray-600 leading-relaxed">{{ $metrics['health_advice']['生活建議'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- 目標進度 -->
                @if($activeGoal)
                    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">🎯 目標進度</h3>
                        <div class="text-center">
                            <div class="text-4xl mb-4">📊</div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">目標體重: {{ $activeGoal->target_weight }} 公斤</h4>
                            <p class="text-gray-600 mb-4">目標日期: {{ $activeGoal->target_date->format('Y年m月d日') }}</p>
                            
                            @php
                                $currentWeight = $metrics['weight'];
                                $targetWeight = $activeGoal->target_weight;
                                $weightDifference = $currentWeight - $targetWeight;
                                $daysRemaining = now()->diffInDays($activeGoal->target_date);
                            @endphp
                            
                            <div class="bg-gray-200 rounded-full h-4 mb-4">
                                <div class="bg-indigo-600 h-4 rounded-full" style="width: {{ min(100, max(0, (abs($weightDifference) / max(abs($weightDifference), 1)) * 100)) }}%"></div>
                            </div>
                            
                            <p class="text-lg font-semibold 
                                @if($weightDifference > 0) text-red-600
                                @elseif($weightDifference < 0) text-green-600
                                @else text-green-600
                                @endif">
                                @if($weightDifference > 0)
                                    還需要減重 {{ $weightDifference }} 公斤
                                @elseif($weightDifference < 0)
                                    已超過目標 {{ abs($weightDifference) }} 公斤
                                @else
                                    已達成目標！
                                @endif
                            </p>
                            
                            @if($daysRemaining > 0)
                                <p class="text-gray-600 mt-2">距離目標日期還有 {{ $daysRemaining }} 天</p>
                            @else
                                <p class="text-gray-600 mt-2">目標日期已過</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- BMI 參考表 -->
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">📋 BMI 參考表</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BMI 範圍</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分類</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">健康風險</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">< 18.5</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">體重過輕</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">營養不良風險</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18.5 - 24</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">正常體重</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">健康範圍</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">24 - 27</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">體重過重</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">輕微健康風險</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">≥ 27</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">肥胖</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">高健康風險</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- 無數據提示 -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">🏥</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">暫無健康數據</h3>
                    <p class="text-gray-600 mb-6">{{ $message ?? '請先記錄體重數據以查看健康指標' }}</p>
                    <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">
                        開始記錄體重
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
