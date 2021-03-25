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
                    <form method="POST" action="{{ route('record') }}">
                        @csrf
                    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 text-xl bg-white">
                        記錄輸入：
                    </div>
                    {{-- 測試輸入日期 --}}
                    <div>
                        <x-label for="record_at" :value="__('記錄日期')" />
            
                        <x-input id="record_at" class="block mt-1 w-full" type="date" name="record_at" value="{{date('Y-m-d')}}" required
                            autofocus />
                        {{-- <x-input id="record_at" class="block mt-1 w-full" type="text" name="record_at" :value="old('記錄日期')??date('Y/m/d')" required
                            autofocus /> --}}
                    </div>
                    {{-- 測試輸入體重 --}}
                    <div class="mt-4">
                        <x-label for="weight" :value="__('體重')" />
            
                        <x-input id="weight" class="block mt-1 w-full" type="text" name="weight" :value="old('體重')" required />
                    </div>
                    {{-- 送出記錄者id --}}
<input type="hidden" name="user" value={{ Auth::user()->id }}>
                    <x-button class="ml-4 mt-2">
                        {{ __('送出') }}
                    </x-button>
                </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
