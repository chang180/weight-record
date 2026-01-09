<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <!-- 同時支援 Vite 和傳統方式載入 CSS -->
        @if (file_exists(public_path('css/app.css')))
            <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        
        @livewireStyles

        <!-- 備用樣式，確保基本顯示正常 -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
                margin: 0;
                padding: 0;
            }
            .min-h-screen { min-height: 100vh; }
            .bg-gray-100 { background-color: #f3f4f6; }
            .bg-white { background-color: white; }
            .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
            .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .max-w-7xl { max-width: 80rem; }
            .font-bold { font-weight: 700; }
            .text-2xl { font-size: 1.5rem; line-height: 2rem; }
            .text-indigo-700 { color: #4338ca; }
            .tracking-tight { letter-spacing: -0.025em; }
            .py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
            .bg-gray-50 { background-color: #f9fafb; }
            .max-w-xl { max-width: 36rem; }
            .bg-white { background-color: white; }
            .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
            .rounded-xl { border-radius: 0.75rem; }
            .p-8 { padding: 2rem; }
            .space-y-6 > * + * { margin-top: 1.5rem; }
            .block { display: block; }
            .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
            .font-semibold { font-weight: 600; }
            .text-gray-700 { color: #374151; }
            .mb-2 { margin-bottom: 0.5rem; }
            .w-full { width: 100%; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .border { border-width: 1px; }
            .border-gray-300 { border-color: #d1d5db; }
            .rounded-lg { border-radius: 0.5rem; }
            .flex { display: flex; }
            .justify-end { justify-content: flex-end; }
            .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .bg-indigo-600 { background-color: #4f46e5; }
            .text-white { color: white; }
            .font-bold { font-weight: 700; }
            .rounded-lg { border-radius: 0.5rem; }
            .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
            .hover\:bg-indigo-700:hover { background-color: #4338ca; }
            .transition { transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }

            @media (min-width: 640px) {
                .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            }

            @media (min-width: 1024px) {
                .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
    </body>
</html>
