<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>使用條款 - 體重記錄系統</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="antialiased font-sans bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">使用條款</h1>
            <p class="text-gray-600 mb-8">最後更新：{{ date('Y年m月d日') }}</p>

            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">1. 服務說明</h2>
                <p class="text-gray-700 mb-6">體重記錄系統是一個免費的健康管理工具。</p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">2. 用戶責任</h2>
                <p class="text-gray-700 mb-6">使用本服務時，請提供真實、準確的體重數據。</p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">3. 免責聲明</h2>
                <p class="text-gray-700 mb-6">本服務僅供參考，不應替代專業醫療建議。</p>

                <div class="mt-8">
                    <a href="/" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">返回首頁</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
