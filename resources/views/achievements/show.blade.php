<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('achievements.index') }}" class="mr-4 text-indigo-600 hover:text-indigo-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
                成就詳情
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:achievement-show :achievement="$achievement" />
        </div>
    </div>
</x-app-layout>
