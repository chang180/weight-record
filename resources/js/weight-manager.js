/**
 * Weight Manager - AJAX 功能處理
 */

class WeightManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupNotifications();
    }

    setupEventListeners() {
        // 新增記錄表單
        const storeForm = document.getElementById('weight-store-form');
        if (storeForm) {
            storeForm.addEventListener('submit', (e) => this.handleStoreSubmit(e));
        }

        // 更新記錄表單
        document.querySelectorAll('.weight-update-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleUpdateSubmit(e));
        });

        // 刪除記錄表單
        document.querySelectorAll('.weight-delete-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleDeleteSubmit(e));
        });
    }

    setupNotifications() {
        // 創建通知容器
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
    }

    async handleStoreSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');

        try {
            this.showLoading(submitButton);

            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification(data.message, 'success');
                form.reset();
                // 設定今天的日期為預設值
                const dateInput = form.querySelector('input[name="record_at"]');
                if (dateInput) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }
            } else {
                throw new Error(data.message || '操作失敗');
            }
        } catch (error) {
            this.showNotification(error.message || '操作失敗，請稍後再試', 'error');
        } finally {
            this.hideLoading(submitButton);
        }
    }

    async handleUpdateSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');

        try {
            this.showLoading(submitButton);

            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification(data.message, 'success');
            } else {
                throw new Error(data.message || '更新失敗');
            }
        } catch (error) {
            this.showNotification(error.message || '更新失敗，請稍後再試', 'error');
        } finally {
            this.hideLoading(submitButton);
        }
    }

    async handleDeleteSubmit(e) {
        e.preventDefault();
        const form = e.target;

        if (!confirm('確定要刪除這筆記錄嗎？')) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');

        try {
            this.showLoading(submitButton);

            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification(data.message, 'success');
                // 移除該行
                const row = form.closest('tr');
                if (row) {
                    row.remove();
                }
            } else {
                throw new Error(data.message || '刪除失敗');
            }
        } catch (error) {
            this.showNotification(error.message || '刪除失敗，請稍後再試', 'error');
        } finally {
            this.hideLoading(submitButton);
        }
    }

    showLoading(button) {
        const originalText = button.textContent;
        button.dataset.originalText = originalText;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            處理中...
        `;
    }

    hideLoading(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || '送出';
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');

        const typeClasses = {
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            info: 'bg-blue-50 border-blue-200 text-blue-800'
        };

        notification.className = `
            ${typeClasses[type]}
            border px-4 py-3 rounded-lg shadow-lg transform transition-all duration-500 opacity-0 translate-x-full
        `;

        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()"
                        class="text-current hover:opacity-75 ml-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(notification);

        // 動畫效果
        setTimeout(() => {
            notification.classList.remove('opacity-0', 'translate-x-full');
        }, 100);

        // 自動移除
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => notification.remove(), 500);
            }
        }, 5000);
    }
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    new WeightManager();
});