<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            🎯 設定體重目標
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <form action="{{ route('goals.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- 目標類型 -->
                    <div>
                        <label for="goal_type" class="block text-sm font-medium text-gray-700 mb-2">
                            目標類型 <span class="text-red-500">*</span>
                        </label>
                        <select name="goal_type" id="goal_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('goal_type') border-red-500 @enderror"
                                required>
                            <option value="">請選擇目標類型</option>
                            <option value="lose" {{ old('goal_type') == 'lose' ? 'selected' : '' }}>減重</option>
                            <option value="maintain" {{ old('goal_type') == 'maintain' ? 'selected' : '' }}>維持體重</option>
                            <option value="gain" {{ old('goal_type') == 'gain' ? 'selected' : '' }}>增重</option>
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
                        <input type="number" name="target_weight" id="target_weight" 
                               value="{{ old('target_weight') }}"
                               step="0.1" min="30" max="200"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('target_weight') border-red-500 @enderror"
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
                        <input type="date" name="target_date" id="target_date" 
                               value="{{ old('target_date') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('target_date') border-red-500 @enderror"
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
                        <textarea name="description" id="description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                                  placeholder="描述您的體重目標和計劃...">{{ old('description') }}</textarea>
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
                            設定目標
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
