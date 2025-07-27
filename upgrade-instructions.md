# Laravel 8 到 Laravel 12 升級指南

## 前置作業

確保您的 PHP 版本為 8.2 或更高版本：
```bash
php -v
```

## 升級步驟

1. 更新 composer.json 檔案（已完成）

2. 執行 Composer 更新：
```bash
composer update
```

3. 清除快取：
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

4. 執行遷移：
```bash
php artisan migrate
```

## 需要注意的重大變更

### 1. 中介層變更
Laravel 12 中的中介層有所變更，請檢查 `app/Http/Kernel.php` 檔案，確保中介層正確設定。

### 2. 路由變更
Laravel 12 的路由定義方式可能與 Laravel 8 有所不同，請檢查 `routes` 目錄下的檔案。

### 3. 認證系統變更
如果您使用了 Laravel Breeze 或其他認證套件，可能需要更新這些套件並調整相關程式碼。

### 4. 模型與資料庫變更
檢查所有模型類別，確保它們與 Laravel 12 相容。

### 5. 環境檔案
檢查 `.env` 檔案，確保它包含所有必要的環境變數。

## 可能需要手動修改的檔案

1. `app/Exceptions/Handler.php`
2. `app/Http/Kernel.php`
3. `app/Providers/RouteServiceProvider.php`
4. `config` 目錄下的配置檔案

## 測試

完成升級後，請徹底測試您的應用程式，確保所有功能都正常運作。

```bash
php artisan test
```

## 如果遇到問題

如果在升級過程中遇到問題，請參考 Laravel 官方文檔：
- [Laravel 9 升級指南](https://laravel.com/docs/9.x/upgrade)
- [Laravel 10 升級指南](https://laravel.com/docs/10.x/upgrade)
- [Laravel 11 升級指南](https://laravel.com/docs/11.x/upgrade)
- [Laravel 12 升級指南](https://laravel.com/docs/12.x/upgrade)