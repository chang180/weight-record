<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            📊 遊戲化統計
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 時間範圍選擇 -->
            <div class="mb-6 bg-white shadow-md rounded-xl p-4">
                <label for="stats-period" class="block text-sm font-medium text-gray-700 mb-2">時間範圍</label>
                <select id="stats-period" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="updateStatsPeriod(this.value)">
                    <option value="7">最近 7 天</option>
                    <option value="30" selected>最近 30 天</option>
                    <option value="90">最近 90 天</option>
                    <option value="180">最近半年</option>
                </select>
            </div>

            <!-- 積分趨勢圖 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-yellow-400 to-orange-500">
                    <h3 class="text-lg font-bold text-white">💰 積分趨勢</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="h-64 sm:h-96 rounded-lg">
                        <canvas id="pointsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 任務完成率圓餅圖 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-green-400 to-blue-500">
                    <h3 class="text-lg font-bold text-white">✅ 任務完成率</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div class="h-64 sm:h-80 rounded-lg">
                            <canvas id="completionChart"></canvas>
                        </div>
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-green-600" id="completion-rate">0%</div>
                                <div class="text-sm text-gray-600 mt-1">完成率</div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                                        <span class="text-sm font-medium">已完成</span>
                                    </div>
                                    <span class="text-sm font-bold" id="completed-count">0</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                        <span class="text-sm font-medium">部分完成</span>
                                    </div>
                                    <span class="text-sm font-bold" id="partial-count">0</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-gray-400 rounded"></div>
                                        <span class="text-sm font-medium">未完成</span>
                                    </div>
                                    <span class="text-sm font-bold" id="none-count">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 連續達成天數趨勢 -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-red-400 to-pink-500">
                    <h3 class="text-lg font-bold text-white">🔥 連續達成天數趨勢</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="h-64 sm:h-96 rounded-lg">
                        <canvas id="streakChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-2xl font-bold text-red-600" id="current-streak">0</div>
                            <div class="text-sm text-gray-600 mt-1">當前連續</div>
                        </div>
                        <div class="text-center p-4 bg-pink-50 rounded-lg">
                            <div class="text-2xl font-bold text-pink-600" id="max-streak">0</div>
                            <div class="text-sm text-gray-600 mt-1">最高連續</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        let pointsChart = null;
        let completionChart = null;
        let streakChart = null;
        let currentPeriod = 30;

        // 初始化圖表
        document.addEventListener('DOMContentLoaded', function() {
            loadStats(currentPeriod);
        });

        // 更新時間範圍
        function updateStatsPeriod(days) {
            currentPeriod = parseInt(days);
            loadStats(currentPeriod);
        }

        // 載入統計數據
        async function loadStats(days) {
            try {
                // 載入積分趨勢
                const pointsResponse = await fetch(`/api/gamification/stats/points-trend?days=${days}`);
                const pointsData = await pointsResponse.json();
                updatePointsChart(pointsData);

                // 載入任務完成率
                const completionResponse = await fetch(`/api/gamification/stats/task-completion?days=${days}`);
                const completionData = await completionResponse.json();
                updateCompletionChart(completionData);

                // 載入連續達成趨勢
                const streakResponse = await fetch(`/api/gamification/stats/streak-trend?days=${days}`);
                const streakData = await streakResponse.json();
                updateStreakChart(streakData);
            } catch (error) {
                console.error('載入統計數據失敗:', error);
            }
        }

        // 更新積分趨勢圖
        function updatePointsChart(data) {
            const ctx = document.getElementById('pointsChart').getContext('2d');
            
            if (pointsChart) {
                pointsChart.destroy();
            }

            pointsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: '每日積分',
                            data: data.daily_points,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                        },
                        {
                            label: '週任務積分',
                            data: data.weekly_points,
                            borderColor: 'rgb(234, 179, 8)',
                            backgroundColor: 'rgba(234, 179, 8, 0.1)',
                            tension: 0.1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: `總積分: ${data.total_points} 分`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // 更新任務完成率圖
        function updateCompletionChart(data) {
            const ctx = document.getElementById('completionChart').getContext('2d');
            
            if (completionChart) {
                completionChart.destroy();
            }

            completionChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['已完成', '部分完成', '未完成'],
                    datasets: [{
                        data: [data.completed, data.partial, data.none],
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // 更新統計數字
            document.getElementById('completion-rate').textContent = data.rate + '%';
            document.getElementById('completed-count').textContent = data.completed;
            document.getElementById('partial-count').textContent = data.partial;
            document.getElementById('none-count').textContent = data.none;
        }

        // 更新連續達成趨勢圖
        function updateStreakChart(data) {
            const ctx = document.getElementById('streakChart').getContext('2d');
            
            if (streakChart) {
                streakChart.destroy();
            }

            streakChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '連續達成天數',
                        data: data.streaks,
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // 更新統計數字
            document.getElementById('current-streak').textContent = data.current_streak;
            document.getElementById('max-streak').textContent = data.max_streak;
        }
    </script>
</x-app-layout>
