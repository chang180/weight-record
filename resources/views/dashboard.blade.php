<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            {{ __('輸入體重記錄') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 新增記錄表單 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-indigo-600">
                    <h3 class="text-lg font-bold text-white">新增體重記錄</h3>
                </div>
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="weight-store-form" method="POST" action="{{ route('weights.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="record_at" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('記錄日期') }}</label>
                            <input id="record_at" name="record_at" type="date" value="{{ date('Y-m-d') }}" required autofocus
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                        </div>
                        <div>
                            <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('體重 (kg)') }}</label>
                            <input id="weight" name="weight" type="number" step="0.1" min="0" :value="old('體重')" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                placeholder="例如：68.5" />
                        </div>
                        <div>
                            <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('備註') }}</label>
                            <textarea id="note" name="note" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                placeholder="可選：記錄當天的飲食、運動或其他情況"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300 transform hover:-translate-y-1">
                                {{ __('送出記錄') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 體重目標設定 -->
            @php
                $activeGoal = auth()->user()->activeWeightGoal;
            @endphp
            
            @if($activeGoal)
                <div class="mt-6 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-purple-800">🎯 當前目標</h3>
                        <a href="{{ route('goals.index') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">管理目標</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $activeGoal->target_weight }} 公斤</div>
                            <div class="text-sm text-purple-500">目標體重</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $activeGoal->target_date->format('Y/m/d') }}</div>
                            <div class="text-sm text-purple-500">目標日期</div>
                        </div>
                        <div class="text-center">
                            @php
                                $currentWeight = auth()->user()->weights()->latest('record_at')->first()?->weight ?? 0;
                                $progress = $currentWeight > 0 ? min(100, max(0, (abs($currentWeight - $activeGoal->target_weight) / max(abs($currentWeight - $activeGoal->target_weight), 1)) * 100)) : 0;
                            @endphp
                            <div class="text-2xl font-bold text-purple-600">{{ round($progress) }}%</div>
                            <div class="text-sm text-purple-500">進度</div>
                        </div>
                    </div>
                    @if($activeGoal->description)
                        <div class="mt-4 p-3 bg-white rounded-lg">
                            <p class="text-sm text-gray-600">{{ $activeGoal->description }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6 border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-indigo-800">🎯 設定體重目標</h3>
                            <p class="text-sm text-indigo-600 mt-1">設定目標可以幫助您更好地管理體重</p>
                        </div>
                        <a href="{{ route('goals.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition duration-300">
                            設定目標
                        </a>
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
                        <h3 class="text-sm font-medium text-blue-800">健康小提示</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>每天同一時間測量體重，可以獲得更準確的變化趨勢。建議在早晨起床後、進食前測量。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
