<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            👤 個人資料設定
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <div class="bg-white rounded-lg shadow-md p-8">
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- 姓名 -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            姓名 <span class="text-red-500">*</span>
                        </label>
                        @php
                            $nameClasses = $errors->has('name')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="text" name="name" id="name" 
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $nameClasses }}"
                               placeholder="請輸入您的姓名"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 電子郵件 -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            電子郵件 <span class="text-red-500">*</span>
                        </label>
                        @php
                            $emailClasses = $errors->has('email')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="email" name="email" id="email" 
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $emailClasses }}"
                               placeholder="請輸入您的電子郵件"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 身高 -->
                    <div>
                        <label for="height" class="block text-sm font-medium text-gray-700 mb-2">
                            身高 (公分)
                        </label>
                        @php
                            $heightClasses = $errors->has('height')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="number" name="height" id="height" 
                               value="{{ old('height', $user->height) }}"
                               step="0.1" min="100" max="250"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $heightClasses }}"
                               placeholder="例如：170.5">
                        @error('height')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            設定身高後，健康指標分析將提供更準確的 BMI 計算和健康建議
                        </p>
                    </div>

                    <!-- 起始體重 -->
                    <div>
                        <label for="start_weight" class="block text-sm font-medium text-gray-700 mb-2">
                            起始體重 (公斤)
                        </label>
                        @php
                            $startWeightClasses = $errors->has('start_weight')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="number" name="start_weight" id="start_weight" 
                               value="{{ old('start_weight', $user->start_weight) }}"
                               step="0.1" min="30" max="200"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $startWeightClasses }}"
                               placeholder="例如：75.5">
                        @error('start_weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            設定起始體重後，系統將根據此數值計算個人化的體重里程碑成就
                        </p>
                    </div>

                    <!-- 當前資料顯示 -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">當前資料</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">姓名：</span>
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">電子郵件：</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">身高：</span>
                                <span class="font-medium">
                                    @if($user->height)
                                        {{ $user->height }} 公分
                                    @else
                                        未設定
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">起始體重：</span>
                                <span class="font-medium">
                                    @if($user->start_weight)
                                        {{ $user->start_weight }} 公斤
                                    @else
                                        未設定
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">註冊時間：</span>
                                <span class="font-medium">{{ $user->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 提交按鈕 -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                            返回首頁
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                            更新資料
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
