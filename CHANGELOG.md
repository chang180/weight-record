# 變動記錄 (Changelog)

## [2025-09-23] - AJAX 請求修復和功能增強

### 修復 (Bug Fixes)
- **修復 AJAX 請求 `expectsJson()` 問題**
  - 在前端 JavaScript 請求中添加 `Accept: application/json` 頭
  - 確保所有 AJAX 請求正確觸發 `expectsJson()` 方法
  - 修復新增、更新、刪除體重記錄的 JSON 響應問題

- **修復 CSV 導出返回類型錯誤**
  - 修正 `WeightController::exportCsv()` 方法的返回類型聲明
  - 從 `Illuminate\Http\StreamedResponse` 改為 `Symfony\Component\HttpFoundation\StreamedResponse`

### 新增功能 (New Features)
- **體重目標管理系統**
  - 新增 `WeightGoal` 模型和相關控制器
  - 實現體重目標的 CRUD 操作
  - 添加目標激活/停用功能
  - 支援目標進度追蹤

- **PDF 和 CSV 導出功能**
  - 實現體重數據 CSV 導出
  - 實現體重數據 PDF 導出
  - 支援中文編碼和格式化

- **數據分析功能**
  - 體重趨勢分析
  - 健康指標計算 (BMI)
  - 目標進度追蹤

### 技術改進 (Technical Improvements)
- 優化前端 AJAX 請求處理
- 改進錯誤處理和用戶反饋
- 增強數據驗證和安全性
- 優化數據庫查詢和快取機制

### 文件變更
- 修改 `resources/js/weight-manager.js` - 修復 AJAX 請求頭
- 修改 `app/Http/Controllers/WeightController.php` - 修復返回類型聲明
- 新增多個體重目標相關文件
- 新增分析視圖和導出功能
