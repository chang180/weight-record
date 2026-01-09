<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            ✏️ 編輯體重目標
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('info'))
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md p-8">
                <form action="{{ route('goals.update', $goal) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- 目標類型 -->
                    <div>
                        <label for="goal_type" class="block text-sm font-medium text-gray-700 mb-2">
                            目標類型 <span class="text-red-500">*</span>
                        </label>
                        @php
                            $goalTypeClasses = $errors->has('goal_type')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <select name="goal_type" id="goal_type" 
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $goalTypeClasses }}"
                                required>
                            <option value="">請選擇目標類型</option>
                            <option value="lose" {{ old('goal_type', $goal->goal_type) == 'lose' ? 'selected' : '' }}>減重</option>
                            <option value="maintain" {{ old('goal_type', $goal->goal_type) == 'maintain' ? 'selected' : '' }}>維持體重</option>
                            <option value="gain" {{ old('goal_type', $goal->goal_type) == 'gain' ? 'selected' : '' }}>增重</option>
                        </select>
                        @error('goal_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 目標體重 -->
                    <div>
                        <label for="target_weight" class="block text-sm font-medium text-gray-700 mb-2">
                            目標體重 (公斤) <span class="text-red-500">*</span>
                        </label>
                        @php
                            $targetWeightClasses = $errors->has('target_weight')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="number" name="target_weight" id="target_weight" 
                               value="{{ old('target_weight', $goal->target_weight) }}"
                               step="0.1" min="30" max="200"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $targetWeightClasses }}"
                               placeholder="例如：65.5"
                               required>
                        @error('target_weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 目標日期 -->
                    <div>
                        <label for="target_date" class="block text-sm font-medium text-gray-700 mb-2">
                            目標達成日期 <span class="text-red-500">*</span>
                        </label>
                        @php
                            $targetDateClasses = $errors->has('target_date')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <input type="date" name="target_date" id="target_date" 
                               value="{{ old('target_date', $goal->target_date->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $targetDateClasses }}"
                               required>
                        @error('target_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 目標描述 -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            目標描述
                        </label>
                        @php
                            $descriptionClasses = $errors->has('description')
                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                        @endphp
                        <textarea name="description" id="description" rows="4"
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 {{ $descriptionClasses }}"
                                  placeholder="描述您的體重目標和計劃...">{{ old('description', $goal->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 提交按鈕 -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('goals.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                            取消
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                            更新目標
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
