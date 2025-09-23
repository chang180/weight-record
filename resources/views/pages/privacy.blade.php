<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>隱私政策 - 體重記錄系統</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="antialiased font-sans bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">隱私政策</h1>
            <p class="text-gray-600 mb-8">最後更新：{{ date('Y年m月d日') }}</p>

            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">1. 資料收集</h2>
                <p class="text-gray-700 mb-6">我們僅收集您主動提供的體重數據和基本帳戶資訊。</p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">2. 資料使用</h2>
                <p class="text-gray-700 mb-6">您的資料僅用於提供體重記錄和圖表分析服務。</p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">3. 資料保護</h2>
                <p class="text-gray-700 mb-6">我們採用銀行級加密技術保護您的資料。</p>

                <div class="mt-8">
                    <a href="/" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">返回首頁</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
