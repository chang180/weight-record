/**
 * Chart Enhancer - 增強圖表互動性
 */

class ChartEnhancer {
    constructor() {
        this.animationDuration = 500;
        this.init();
    }

    init() {
        this.setupResponsiveHandlers();
        this.setupAdvancedInteractions();
        this.setupDataAnalytics();
    }

    setupResponsiveHandlers() {
        // 監聽視窗大小變化
        window.addEventListener('resize', this.debounce(() => {
            if (window.myChart) {
                window.myChart.resize();
            }
        }, 250));

        // 監聽設備方向變化
        if (screen.orientation) {
            screen.orientation.addEventListener('change', () => {
                setTimeout(() => {
                    if (window.myChart) {
                        window.myChart.resize();
                    }
                }, 100);
            });
        }
    }

    setupAdvancedInteractions() {
        // 添加鍵盤快捷鍵
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName.toLowerCase() === 'input') return;

            switch(e.key) {
                case '1':
                    this.changePeriod('7');
                    break;
                case '2':
                    this.changePeriod('30');
                    break;
                case '3':
                    this.changePeriod('90');
                    break;
                case '4':
                    this.changePeriod('all');
                    break;
                case 'l':
                    this.changeChartType('line');
                    break;
                case 'b':
                    this.changeChartType('bar');
                    break;
                case 'd':
                    this.downloadChart();
                    break;
            }
        });

        // 添加觸控手勢支援
        this.setupTouchGestures();
    }

    setupTouchGestures() {
        const chartContainer = document.getElementById('myChart');
        if (!chartContainer) return;

        let startX = 0;
        let startY = 0;

        chartContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });

        chartContainer.addEventListener('touchmove', (e) => {
            e.preventDefault(); // 防止頁面滾動
        }, { passive: false });

        chartContainer.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;

            const deltaX = endX - startX;
            const deltaY = endY - startY;

            // 檢測水平滑動
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    // 向右滑動 - 切換到上一個時間範圍
                    this.previousPeriod();
                } else {
                    // 向左滑動 - 切換到下一個時間範圍
                    this.nextPeriod();
                }
            }
        });
    }

    setupDataAnalytics() {
        // 添加數據分析工具提示
        this.addAnalyticsTooltips();

        // 設置實時數據檢查
        this.setupRealTimeUpdates();
    }

    addAnalyticsTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');

        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });

            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
        });
    }

    setupRealTimeUpdates() {
        // 檢查是否有新數據（每 5 分鐘檢查一次）
        setInterval(() => {
            this.checkForNewData();
        }, 300000);
    }

    async checkForNewData() {
        try {
            const response = await window.axios.get('/api/weights/latest', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = response.data;

            // 如果有新數據，更新圖表
            if (data.hasNewData) {
                this.updateChartWithNewData(data.weights);
            }
        } catch (error) {
            console.log('檢查新數據時發生錯誤:', error);
        }
    }

    updateChartWithNewData(newWeights) {
        if (!window.myChart || !window.allWeights) return;

        // 更新全域數據
        window.allWeights = newWeights;
        window.currentWeights = [...newWeights];

        // 重新繪製圖表
        window.updateChart();

        // 顯示更新通知
        this.showUpdateNotification();
    }

    showUpdateNotification() {
        const notification = document.createElement('div');
        notification.className = `
            fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50
            transform translate-x-full transition-transform duration-300
        `;
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                </svg>
                圖表已更新為最新數據
            </div>
        `;

        document.body.appendChild(notification);

        // 動畫顯示
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // 自動隱藏
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    changePeriod(period) {
        const select = document.getElementById('chart-period');
        if (select) {
            select.value = period;
            window.updateChartPeriod(period);
            this.highlightSelection(select);
        }
    }

    changeChartType(type) {
        const select = document.getElementById('chart-type');
        if (select) {
            select.value = type;
            window.updateChartType(type);
            this.highlightSelection(select);
        }
    }

    downloadChart() {
        const button = document.getElementById('download-chart');
        if (button) {
            button.click();
            this.highlightSelection(button);
        }
    }

    previousPeriod() {
        const select = document.getElementById('chart-period');
        if (!select) return;

        const options = Array.from(select.options);
        const currentIndex = select.selectedIndex;

        if (currentIndex > 0) {
            const newValue = options[currentIndex - 1].value;
            this.changePeriod(newValue);
        }
    }

    nextPeriod() {
        const select = document.getElementById('chart-period');
        if (!select) return;

        const options = Array.from(select.options);
        const currentIndex = select.selectedIndex;

        if (currentIndex < options.length - 1) {
            const newValue = options[currentIndex + 1].value;
            this.changePeriod(newValue);
        }
    }

    highlightSelection(element) {
        element.classList.add('ring-2', 'ring-indigo-300', 'ring-opacity-50');
        setTimeout(() => {
            element.classList.remove('ring-2', 'ring-indigo-300', 'ring-opacity-50');
        }, 1000);
    }

    showTooltip(target, text) {
        const tooltip = document.createElement('div');
        tooltip.id = 'chart-tooltip';
        tooltip.className = `
            absolute z-50 bg-gray-800 text-white text-sm px-3 py-2 rounded-md shadow-lg
            transform -translate-x-1/2 -translate-y-full pointer-events-none
        `;
        tooltip.textContent = text;

        const rect = target.getBoundingClientRect();
        tooltip.style.left = rect.left + rect.width / 2 + 'px';
        tooltip.style.top = rect.top - 10 + 'px';

        document.body.appendChild(tooltip);
    }

    hideTooltip() {
        const tooltip = document.getElementById('chart-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // 添加平滑滾動到統計卡片
    scrollToStats() {
        const statsSection = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-4');
        if (statsSection) {
            statsSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    // 添加圖表動畫效果
    animateChart() {
        if (!window.myChart) return;

        window.myChart.options.animation = {
            duration: this.animationDuration,
            easing: 'easeInOutQuart'
        };

        window.myChart.update();
    }
}

// 添加快捷鍵說明
function addKeyboardShortcutsHelp() {
    const helpButton = document.createElement('button');
    helpButton.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    `;
    helpButton.className = 'p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition';
    helpButton.title = '顯示快捷鍵說明';

    helpButton.addEventListener('click', () => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">鍵盤快捷鍵</h3>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>1-4</span><span>切換時間範圍</span></div>
                    <div class="flex justify-between"><span>L</span><span>切換到折線圖</span></div>
                    <div class="flex justify-between"><span>B</span><span>切換到柱狀圖</span></div>
                    <div class="flex justify-between"><span>D</span><span>下載圖表</span></div>
                    <div class="flex justify-between"><span>左右滑動</span><span>切換時間範圍</span></div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // 點擊背景關閉
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    });

    // 添加到圖表控制區域
    const controlArea = document.querySelector('.mb-6 .flex-wrap:last-child');
    if (controlArea) {
        controlArea.appendChild(helpButton);
    }
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('myChart')) {
        new ChartEnhancer();
        addKeyboardShortcutsHelp();
    }
});