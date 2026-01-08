<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('rewards.index') }}" class="mr-4 text-indigo-600 hover:text-indigo-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
                📜 兌換歷史
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($rewards->count() > 0)
                <!-- 兌換記錄列表 -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        獎勵名稱
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        積分消耗
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        兌換時間
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        備註
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rewards as $reward)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-2xl mr-3">🎁</div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $reward->reward_name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-purple-600 font-bold">-{{ $reward->points_spent }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $reward->redeemed_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $reward->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 分頁 -->
                <div class="mt-6">
                    {{ $rewards->links() }}
                </div>

                <!-- 統計資訊 -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">總兌換次數</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $rewards->total() }}</p>
                            </div>
                            <div class="text-3xl">🎁</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">累計消耗積分</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ auth()->user()->rewards()->sum('points_spent') }}
                                </p>
                            </div>
                            <div class="text-3xl">💎</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">可用積分</p>
                                <p class="text-2xl font-bold text-green-600">{{ auth()->user()->available_points }}</p>
                            </div>
                            <div class="text-3xl">✨</div>
                        </div>
                    </div>
                </div>
            @else
                <!-- 無記錄提示 -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">📜</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">還沒有兌換記錄</h3>
                    <p class="text-gray-600 mb-6">完成任務賺取積分，然後來兌換獎勵吧！</p>
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('daily-logs.index') }}"
                           class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                            前往今日任務
                        </a>
                        <a href="{{ route('rewards.index') }}"
                           class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300">
                            瀏覽獎勵商店
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
