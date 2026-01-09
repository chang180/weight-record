<div>
    <!-- 時間範圍選擇 -->
    <div class="mb-6 bg-white shadow-md rounded-xl p-4">
        <label for="stats-period" class="block text-sm font-medium text-gray-700 mb-2">時間範圍</label>
        <div class="relative">
            <select 
                id="stats-period" 
                wire:model.live="days"
                wire:loading.class="opacity-50"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="7">最近 7 天</option>
            <option value="30">最近 30 天</option>
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
                <canvas 
                    id="pointsChart"
                    x-data="{
                        chart: null,
                        init() {
                            this.initChart();
                            $watch('$wire.pointsData', () => {
                                this.updateChart();
                            });
                        },
                        initChart() {
                            const ctx = this.$el.getContext('2d');
                            const data = $wire.pointsData;
                            
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.labels || [],
                                    datasets: [
                                        {
                                            label: '每日積分',
                                            data: data.daily_points || [],
                                            borderColor: 'rgb(59, 130, 246)',
                                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                            tension: 0.1,
                                        },
                                        {
                                            label: '週任務積分',
                                            data: data.weekly_points || [],
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
                                            text: `總積分: ${data.total_points || 0} 分`
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        },
                        updateChart() {
                            const data = $wire.pointsData;
                            if (this.chart && data) {
                                this.chart.data.labels = data.labels || [];
                                this.chart.data.datasets[0].data = data.daily_points || [];
                                this.chart.data.datasets[1].data = data.weekly_points || [];
                                this.chart.options.plugins.title.text = `總積分: ${data.total_points || 0} 分`;
                                this.chart.update();
                            }
                        }
                    }"
                    wire:ignore></canvas>
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
                    <canvas 
                        id="completionChart"
                        x-data="{
                            chart: null,
                            init() {
                                this.initChart();
                                $watch('$wire.completionData', () => {
                                    this.updateChart();
                                });
                            },
                            initChart() {
                                const ctx = this.$el.getContext('2d');
                                const data = $wire.completionData;
                                
                                if (this.chart) {
                                    this.chart.destroy();
                                }
                                
                                this.chart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: ['已完成', '部分完成', '未完成'],
                                        datasets: [{
                                            data: [data.completed || 0, data.partial || 0, data.none || 0],
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
                                
                                this.updateStats(data);
                            },
                            updateChart() {
                                const data = $wire.completionData;
                                if (this.chart && data) {
                                    this.chart.data.datasets[0].data = [data.completed || 0, data.partial || 0, data.none || 0];
                                    this.chart.update();
                                }
                                this.updateStats(data);
                            },
                            updateStats(data) {
                                if (data) {
                                    document.getElementById('completion-rate').textContent = (data.rate || 0) + '%';
                                    document.getElementById('completed-count').textContent = data.completed || 0;
                                    document.getElementById('partial-count').textContent = data.partial || 0;
                                    document.getElementById('none-count').textContent = data.none || 0;
                                }
                            }
                        }"
                        wire:ignore></canvas>
                </div>
                <div class="flex flex-col justify-center space-y-4">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600" id="completion-rate">{{ $completionData['rate'] ?? 0 }}%</div>
                        <div class="text-sm text-gray-600 mt-1">完成率</div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span class="text-sm font-medium">已完成</span>
                            </div>
                            <span class="text-sm font-bold" id="completed-count">{{ $completionData['completed'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                <span class="text-sm font-medium">部分完成</span>
                            </div>
                            <span class="text-sm font-bold" id="partial-count">{{ $completionData['partial'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-gray-400 rounded"></div>
                                <span class="text-sm font-medium">未完成</span>
                            </div>
                            <span class="text-sm font-bold" id="none-count">{{ $completionData['none'] ?? 0 }}</span>
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
                <canvas 
                    id="streakChart"
                    x-data="{
                        chart: null,
                        init() {
                            this.initChart();
                            $watch('$wire.streakData', () => {
                                this.updateChart();
                            });
                        },
                        initChart() {
                            const ctx = this.$el.getContext('2d');
                            const data = $wire.streakData;
                            
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            
                            this.chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: data.labels || [],
                                    datasets: [{
                                        label: '連續達成天數',
                                        data: data.streaks || [],
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
                            
                            this.updateStats(data);
                        },
                        updateChart() {
                            const data = $wire.streakData;
                            if (this.chart && data) {
                                this.chart.data.labels = data.labels || [];
                                this.chart.data.datasets[0].data = data.streaks || [];
                                this.chart.update();
                            }
                            this.updateStats(data);
                        },
                        updateStats(data) {
                            if (data) {
                                document.getElementById('current-streak').textContent = data.current_streak || 0;
                                document.getElementById('max-streak').textContent = data.max_streak || 0;
                            }
                        }
                    }"
                    wire:ignore></canvas>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600" id="current-streak">{{ $streakData['current_streak'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">當前連續</div>
                </div>
                <div class="text-center p-4 bg-pink-50 rounded-lg">
                    <div class="text-2xl font-bold text-pink-600" id="max-streak">{{ $streakData['max_streak'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">最高連續</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</div>
