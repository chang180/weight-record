@extends('layouts.app')

@section('content')
<div class="py-10 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($header))
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-indigo-700 mb-2">
                    {{ $header }}
                </h1>
            </div>
        @endif
        
        {{ $slot }}
    </div>
</div>
@endsection
