<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            {{ __('體重記錄列表') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 日期範圍篩選 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-indigo-600">
                    <h3 class="text-lg font-bold text-white">篩選記錄</h3>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('record') }}" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('開始日期') }}</label>
                            <input id="start_date" name="start_date" type="date" value="{{ request('start_date') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('結束日期') }}</label>
                            <input id="end_date" name="end_date" type="date" value="{{ request('end_date') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300">
                                {{ __('篩選') }}
                            </button>
                            <a href="{{ route('record') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg shadow hover:bg-gray-300 transition duration-300">
                                {{ __('重置') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-indigo-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">所有體重記錄</h3>
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-white text-indigo-600 font-bold rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        {{ __('新增記錄') }}
                    </a>
                </div>
                <div class="p-6">
                    @if (count($weights) != 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">體重 (kg)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備註</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($weights as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="/edit/{{ $item->id }}" method="POST" class="flex items-center space-x-2">
                                                @csrf
                                                <div class="text-sm text-gray-900 font-medium">{{ \Carbon\Carbon::parse($item->record_at)->format('Y-m-d') }}</div>
                                                <input type="hidden" name="record_at" value="{{ $item->record_at }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" step="0.1" min="0" name="weight" value="{{ $item->weight }}"
                                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm w-24">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="text" name="note" value="{{ $item->note ?? '' }}" placeholder="無備註"
                                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm w-full">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <input type="hidden" name="user" value="{{ Auth::user()->id }}">
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-medium mr-2 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    儲存
                                                </button>
                                            </form>
                                            <a href="/delete/{{ $item->id }}" onclick="return confirm('確定要刪除這筆記錄嗎？')" class="text-red-600 hover:text-red-900 font-medium inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                刪除
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $weights->appends(request()->except('page'))->links() }}
                        </div>
                    @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">目前還沒有記錄</h3>
                            <p class="mt-1 text-sm text-gray-500">開始記錄您的體重變化吧！</p>
                            <div class="mt-6">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    新增第一筆記錄
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 健康小提示 -->
            <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">體重管理小提示</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>定期記錄體重可以幫助您更好地了解自己的健康狀況，並及時調整生活習慣。建議每週至少記錄2-3次。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
