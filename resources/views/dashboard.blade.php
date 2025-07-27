
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            {{ __('體重記錄') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-xl mx-auto">
            <div class="bg-white shadow-lg rounded-xl p-8">
                <form method="POST" action="{{ route('record') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="record_at" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('記錄日期') }}</label>
                        <input id="record_at" name="record_at" type="date" value="{{ date('Y-m-d') }}" required autofocus
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('體重 (kg)') }}</label>
                        <input id="weight" name="weight" type="number" step="0.1" min="0" :value="old('體重')" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                    </div>
                    <input type="hidden" name="user" value="{{ Auth::user()->id }}">
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition">
                            {{ __('送出') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
