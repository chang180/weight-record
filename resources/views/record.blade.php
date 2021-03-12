<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('所有記錄') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- {{ dd($weights) }} --}}
                    @if (count($weights)!=0)
                        @foreach ($weights as $item)
                            {{-- {{ $item->record_at . ',' . $item->weight }} --}}
                            <form action="/edit/{{ $item->id }}" method="POST" class="mb-1">
                                @csrf
                                <input type="date" name="record_at" value="{{ $item->record_at}}">
                                <input type="text" name="weight" value="{{$item->weight }}">
                                <input type="hidden" name="user" value={{ Auth::user()->id }}>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">修改</button>
                                <a href="/delete/{{ $item->id }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">刪除</a>
                            </form>
                        @endforeach
                        {{ $weights->links() }}
                    @else
                        <h1>目前還沒有記錄</h1>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
