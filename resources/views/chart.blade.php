<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-indigo-700 tracking-tight">
            {{ __('體重變化統計圖') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (count($weights) != 0)
                <!-- 圖表控制區域 -->
                <div class="mb-6 bg-white shadow-md rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <label for="chart-period" class="block text-sm font-medium text-gray-700 mb-1">時間範圍</label>
                            <select id="chart-period" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="updateChartPeriod(this.value)">
                                <option value="7">最近 7 天</option>
                                <option value="30">最近 30 天</option>
                                <option value="90">最近 90 天</option>
                                <option value="180">最近半年</option>
                                <option value="365">最近一年</option>
                                <option value="all" selected>全部記錄</option>
                            </select>
                        </div>
                        <div>
                            <label for="chart-type" class="block text-sm font-medium text-gray-700 mb-1">圖表類型</label>
                            <select id="chart-type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="updateChartType(this.value)">
                                <option value="line">折線圖</option>
                                <option value="bar">柱狀圖</option>
                            </select>
                        </div>
                        <div>
                            <label for="data-density" class="block text-sm font-medium text-gray-700 mb-1">資料密度</label>
                            <select id="data-density" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="updateDataDensity(this.value)">
                                <option value="all" selected>顯示全部</option>
                                <option value="weekly">每週平均</option>
                                <option value="monthly">每月平均</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button id="download-chart" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium shadow hover:bg-green-700 transition mr-2">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                下載圖表
                            </span>
                        </button>
                    </div>
                </div>

                <!-- 主圖表區域 -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
                    <div class="px-6 py-4 bg-indigo-600">
                        <h3 class="text-lg font-bold text-white">體重變化趨勢</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-96 rounded-lg">
                            <canvas id="myChart" height="100%" width="100%"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 統計卡片區域 -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- 平均體重卡片 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">平均體重</div>
                                    <div class="mt-1 text-2xl font-semibold text-gray-900" id="avg-weight">計算中...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 最高體重卡片 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">最高體重</div>
                                    <div class="mt-1 text-2xl font-semibold text-gray-900" id="max-weight">計算中...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 最低體重卡片 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">最低體重</div>
                                    <div class="mt-1 text-2xl font-semibold text-gray-900" id="min-weight">計算中...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 總體變化卡片 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">總體變化</div>
                                    <div class="mt-1 text-2xl font-semibold" id="total-change">計算中...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BMI 計算器 -->
                <div class="mt-8 bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-4 bg-indigo-600">
                        <h3 class="text-lg font-bold text-white">BMI 計算器</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="height" class="block text-sm font-semibold text-gray-700 mb-2">身高 (cm)</label>
                                <input id="height" type="number" min="100" max="250" placeholder="例如：175"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                            </div>
                            <div>
                                <label for="current-weight" class="block text-sm font-semibold text-gray-700 mb-2">目前體重 (kg)</label>
                                <input id="current-weight" type="number" step="0.1" min="30" max="200" placeholder="例如：68.5"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                            </div>
                            <div class="flex items-end">
                                <button id="calculate-bmi" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium shadow hover:bg-indigo-700 transition">
                                    計算 BMI
                                </button>
                            </div>
                        </div>

                        <div id="bmi-result" class="mt-6 hidden">
                            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-500 mb-1">您的 BMI</div>
                                        <div class="text-2xl font-bold" id="bmi-value">-</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-500 mb-1">體重狀態</div>
                                        <div class="text-lg font-semibold" id="bmi-category">-</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-500 mb-1">建議體重範圍</div>
                                        <div class="text-lg font-semibold" id="ideal-weight-range">-</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full" id="bmi-indicator" style="width: 0%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>過輕</span>
                                        <span>正常</span>
                                        <span>過重</span>
                                        <span>肥胖</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-lg rounded-xl p-8 text-center">
                    <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <h1 class="text-2xl font-bold text-gray-700 mb-2">目前還沒有記錄</h1>
                    <p class="text-gray-500 mb-6">開始記錄您的體重，就能在這裡看到變化趨勢</p>
                    <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-300 transform hover:-translate-y-1">
                        開始記錄
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<!-- 載入 Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
    // 獲取體重數據
    var allWeights = {!! json_encode($weights) !!};
    var currentWeights = [...allWeights]; // 當前顯示的數據
    var myChart = null; // 圖表實例
    var chartType = 'line'; // 當前圖表類型
    var dataDensity = 'all'; // 當前數據密度

    // 如果有數據才執行
    if (allWeights && allWeights.length > 0) {
        // 初始化圖表
        initChart();

        // 設置 BMI 計算器
        setupBmiCalculator();

        // 設置下載圖表功能
        setupChartDownload();
    }

    // 初始化圖表
    function initChart() {
        // 準備圖表數據
        let chartData = prepareChartData(currentWeights);

        // 創建圖表
        var ctx = document.getElementById('myChart').getContext('2d');
        myChart = createChart(ctx, chartType, chartData.labels, chartData.data);

        // 計算並顯示統計數據
        calculateStats(chartData.data);
    }

    // 準備圖表數據
    function prepareChartData(weights) {
        let labels = [];
        let data = [];

        // 根據數據密度處理數據
        if (dataDensity === 'all') {
            // 顯示所有數據
            weights.forEach(element => {
                // 格式化日期以更好顯示
                const date = new Date(element.record_at);
                const formattedDate = (date.getMonth() + 1) + '/' + date.getDate();
                labels.push(formattedDate);
                data.push(parseFloat(element.weight));
            });
        } else if (dataDensity === 'weekly') {
            // 按週分組並計算平均值
            const weeklyData = groupDataByPeriod(weights, 'week');
            labels = weeklyData.labels;
            data = weeklyData.data;
        } else if (dataDensity === 'monthly') {
            // 按月分組並計算平均值
            const monthlyData = groupDataByPeriod(weights, 'month');
            labels = monthlyData.labels;
            data = monthlyData.data;
        }

        return { labels, data };
    }

    // 按週或月分組數據
    function groupDataByPeriod(weights, periodType) {
        let groupedData = {};
        let labels = [];
        let data = [];

        weights.forEach(element => {
            const date = new Date(element.record_at);
            let periodKey;

            if (periodType === 'week') {
                // 獲取該日期所在的週數
                const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
                const pastDaysOfYear = (date - firstDayOfYear) / 86400000;
                const weekNumber = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
                periodKey = `${date.getFullYear()}-W${weekNumber}`;

                // 格式化標籤
                if (!groupedData[periodKey]) {
                    labels.push(`第${weekNumber}週`);
                }
            } else if (periodType === 'month') {
                periodKey = `${date.getFullYear()}-${date.getMonth() + 1}`;

                // 格式化標籤
                if (!groupedData[periodKey]) {
                    labels.push(`${date.getMonth() + 1}月`);
                }
            }

            // 將體重添加到相應的週或月
            if (!groupedData[periodKey]) {
                groupedData[periodKey] = {
                    sum: 0,
                    count: 0
                };
            }

            groupedData[periodKey].sum += parseFloat(element.weight);
            groupedData[periodKey].count += 1;
        });

        // 計算每個週或月的平均體重
        Object.keys(groupedData).forEach(key => {
            const avg = groupedData[key].sum / groupedData[key].count;
            data.push(parseFloat(avg.toFixed(1)));
        });

        return { labels, data };
    }

    // 創建圖表
    function createChart(ctx, type, labels, data) {
        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: '體重 (kg)',
                    data: data,
                    backgroundColor: type === 'line' ? 'rgba(79, 70, 229, 0.2)' : 'rgba(79, 70, 229, 0.7)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    tension: 0.1 // 讓線條更平滑
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false,
                            // 設定 y 軸範圍，最小值稍微小於最小體重，最大值稍微大於最大體重
                            suggestedMin: Math.min(...data) - 2,
                            suggestedMax: Math.max(...data) + 2
                        },
                        gridLines: {
                            drawBorder: false
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }]
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        fontColor: '#4B5563',
                        boxWidth: 20,
                        padding: 20
                    }
                },
                tooltips: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    bodyFontColor: '#fff',
                    titleFontColor: '#fff',
                    titleSpacing: 4,
                    titleMarginBottom: 10,
                    bodySpacing: 8,
                    xPadding: 12,
                    yPadding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return tooltipItem.yLabel + ' kg';
                        }
                    }
                }
            }
        });
    }

    // 計算統計數據
    function calculateStats(data) {
        if (data.length === 0) return;

        // 平均體重
        const sum = data.reduce((a, b) => a + b, 0);
        const avg = (sum / data.length).toFixed(1);
        document.getElementById('avg-weight').textContent = avg + ' kg';

        // 最高體重
        const max = Math.max(...data).toFixed(1);
        document.getElementById('max-weight').textContent = max + ' kg';

        // 最低體重
        const min = Math.min(...data).toFixed(1);
        document.getElementById('min-weight').textContent = min + ' kg';

        // 總體變化
        const first = data[0];
        const last = data[data.length - 1];
        const change = (last - first).toFixed(1);
        const changeElement = document.getElementById('total-change');

        // 清除之前的顏色類別
        changeElement.classList.remove('text-green-600', 'text-red-600', 'text-gray-600');

        // 根據變化設置顏色和符號
        if (change < 0) {
            changeElement.textContent = change + ' kg';
            changeElement.classList.add('text-green-600');
        } else if (change > 0) {
            changeElement.textContent = '+' + change + ' kg';
            changeElement.classList.add('text-red-600');
        } else {
            changeElement.textContent = '0 kg';
            changeElement.classList.add('text-gray-600');
        }
    }

    // 更新圖表類型
    window.updateChartType = function(type) {
        chartType = type;
        updateChart();
    };

    // 更新時間範圍
    window.updateChartPeriod = function(days) {
        if (days === 'all') {
            currentWeights = [...allWeights];
        } else {
            // 計算截止日期
            const cutoffDate = new Date();
            cutoffDate.setDate(cutoffDate.getDate() - parseInt(days));

            // 過濾數據
            currentWeights = allWeights.filter(item => {
                const recordDate = new Date(item.record_at);
                return recordDate >= cutoffDate;
            });
        }

        updateChart();
    };

    // 更新數據密度
    window.updateDataDensity = function(density) {
        dataDensity = density;
        updateChart();
    };

    // 更新圖表
    function updateChart() {
        if (myChart) {
            myChart.destroy(); // 銷毀舊圖表
        }

        // 準備新數據
        let chartData = prepareChartData(currentWeights);

        // 創建新圖表
        var ctx = document.getElementById('myChart').getContext('2d');
        myChart = createChart(ctx, chartType, chartData.labels, chartData.data);

        // 更新統計數據
        calculateStats(chartData.data);
    }

    // 設置 BMI 計算器
    function setupBmiCalculator() {
        const heightInput = document.getElementById('height');
        const weightInput = document.getElementById('current-weight');
        const calculateButton = document.getElementById('calculate-bmi');
        const resultDiv = document.getElementById('bmi-result');
        const bmiValue = document.getElementById('bmi-value');
        const bmiCategory = document.getElementById('bmi-category');
        const idealWeightRange = document.getElementById('ideal-weight-range');
        const bmiIndicator = document.getElementById('bmi-indicator');

        // 如果有最新體重記錄，自動填入
        if (allWeights.length > 0) {
            const latestWeight = allWeights[allWeights.length - 1].weight;
            weightInput.value = latestWeight;
        }

        calculateButton.addEventListener('click', function() {
            const height = parseFloat(heightInput.value);
            const weight = parseFloat(weightInput.value);

            if (isNaN(height) || isNaN(weight) || height <= 0 || weight <= 0) {
                alert('請輸入有效的身高和體重');
                return;
            }

            // 計算 BMI
            const heightInMeters = height / 100;
            const bmi = weight / (heightInMeters * heightInMeters);
            const bmiRounded = bmi.toFixed(1);

            // 計算理想體重範圍
            const idealLow = (18.5 * heightInMeters * heightInMeters).toFixed(1);
            const idealHigh = (24 * heightInMeters * heightInMeters).toFixed(1);

            // 設置 BMI 類別
            let category;
            let indicatorColor;
            let indicatorWidth;

            if (bmi < 18.5) {
                category = '體重過輕';
                indicatorColor = 'bg-blue-500';
                indicatorWidth = (bmi / 40) * 100; // 40 作為最大 BMI 值
            } else if (bmi < 24) {
                category = '正常範圍';
                indicatorColor = 'bg-green-500';
                indicatorWidth = (bmi / 40) * 100;
            } else if (bmi < 27) {
                category = '過重';
                indicatorColor = 'bg-yellow-500';
                indicatorWidth = (bmi / 40) * 100;
            } else if (bmi < 30) {
                category = '輕度肥胖';
                indicatorColor = 'bg-orange-500';
                indicatorWidth = (bmi / 40) * 100;
            } else if (bmi < 35) {
                category = '中度肥胖';
                indicatorColor = 'bg-red-500';
                indicatorWidth = (bmi / 40) * 100;
            } else {
                category = '重度肥胖';
                indicatorColor = 'bg-red-700';
                indicatorWidth = (bmi / 40) * 100;
            }

            // 更新顯示
            bmiValue.textContent = bmiRounded;
            bmiCategory.textContent = category;
            idealWeightRange.textContent = `${idealLow} - ${idealHigh} kg`;

            // 更新指示器
            bmiIndicator.style.width = `${Math.min(indicatorWidth, 100)}%`;
            bmiIndicator.className = `h-2.5 rounded-full ${indicatorColor}`;

            // 顯示結果
            resultDiv.classList.remove('hidden');
        });
    }

    // 設置下載圖表功能
    function setupChartDownload() {
        const downloadButton = document.getElementById('download-chart');

        downloadButton.addEventListener('click', function() {
            // 創建一個臨時鏈接
            const link = document.createElement('a');
            link.download = '體重變化圖表.png';

            // 將圖表轉換為圖片
            link.href = myChart.toBase64Image();

            // 點擊鏈接下載圖片
            link.click();
        });
    }
</script>
