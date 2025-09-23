<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>聯絡我們 - 體重記錄系統</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="antialiased font-sans bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">聯絡我們</h1>

            <div class="prose prose-lg max-w-none">
                <p class="text-gray-700 mb-6">如有任何問題或建議，請透過以下方式聯絡我們：</p>

                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">聯絡資訊</h3>
                    <p class="text-gray-700 mb-2">電子郵件：support@weight-tracker.com</p>
                    <p class="text-gray-700 mb-2">回應時間：24小時內回覆</p>
                    <p class="text-gray-700">服務時間：週一至週五 9:00-18:00</p>
                </div>

                <div class="mt-8">
                    <a href="/" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-300">返回首頁</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
