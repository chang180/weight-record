<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
                🎁 獎勵商店
            </h2>
            <a href="{{ route('rewards.history') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
                兌換歷史
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
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

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-800">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 可用積分 -->
            <div class="mb-8 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-8 text-white text-center">
                <p class="text-purple-100 mb-2">您的可用積分</p>
                <p class="text-5xl font-bold mb-2">{{ $availablePoints }}</p>
                <p class="text-purple-100 text-sm">完成更多任務來賺取積分！</p>
            </div>

            <!-- 獎勵列表 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($rewards as $reward)
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

                            <form method="POST" action="{{ route('rewards.store') }}" class="w-full"
                                  onsubmit="return confirm('確定要兌換 {{ $reward['name'] }} 嗎？將消耗 {{ $reward['points'] }} 積分。')">
                                @csrf
                                <input type="hidden" name="reward_type" value="{{ $reward['type'] }}">
                                <input type="hidden" name="reward_name" value="{{ $reward['name'] }}">
                                <input type="hidden" name="points_spent" value="{{ $reward['points'] }}">

                                @if($availablePoints >= $reward['points'])
                                    <button type="submit"
                                            class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition duration-300">
                                        立即兌換
                                    </button>
                                @else
                                    <button type="button" disabled
                                            class="w-full bg-gray-300 text-gray-500 py-3 rounded-lg font-bold cursor-not-allowed">
                                        積分不足
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
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
    </div>
</x-app-layout>
