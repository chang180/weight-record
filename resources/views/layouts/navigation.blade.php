
<nav class="bg-white border-b border-gray-200 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo & Links -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}" class="font-bold text-indigo-600 text-xl tracking-tight hover:text-indigo-800 transition">
                    {{ config('app.name', 'WeightRecord') }}
                </a>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 font-medium transition">{{ __('輸入記錄') }}</a>
                <a href="{{ route('record') }}" class="text-gray-700 hover:text-indigo-600 font-medium transition">{{ __('記錄列表') }}</a>
                <a href="{{ route('chart') }}" class="text-gray-700 hover:text-indigo-600 font-medium transition">{{ __('統計圖') }}</a>
            </div>
            <!-- User Info & Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow hover:bg-indigo-700 transition">
                        {{ __('登出') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
