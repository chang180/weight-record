<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('輸入記錄') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 text-xl bg-white">
                        記錄輸入：
                    </div>
                    {{-- 測試輸入日期 --}}
                    <div>
                        <x-label for="date" :value="__('Date')" />
            
                        <x-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date')" required
                            autofocus />
                    </div>
                    {{-- 測試輸入體重 --}}
                    <div class="mt-4">
                        <x-label for="weight" :value="__('Weight')" />
            
                        <x-input id="weight" class="block mt-1 w-full" type="text" name="weight" :value="old('weight')" required />
                    </div>
                    <x-button class="ml-4 mt-2">
                        {{ __('Submit') }}
                    </x-button>
                </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
