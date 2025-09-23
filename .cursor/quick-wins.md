# 快速優化項目 (Quick Wins)

## 立即可執行的優化 (1-2 小時內完成)

### 1. 程式碼風格修正
**時間**: 30 分鐘
**影響**: 程式碼可讀性

```bash
# 安裝 PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer

# 執行程式碼風格修正
./vendor/bin/php-cs-fixer fix
```

### 2. 添加型別提示
**時間**: 1 小時
**影響**: 程式碼品質和 IDE 支援

**Weight 模型優化**:
```php
// 在 Weight.php 中添加
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user', 'id');
}

// 添加型別提示
protected $fillable = [
    'user',
    'weight', 
    'record_at',
    'note'
];

protected $casts = [
    'record_at' => 'date',
    'weight' => 'decimal:1'
];
```

### 3. 控制器方法型別提示
**時間**: 30 分鐘
**影響**: 程式碼品質

```php
// WeightController.php 方法簽名優化
public function index(Request $request): View
public function store(StoreWeightRequest $request): RedirectResponse
public function show(): View
public function edit(UpdateWeightRequest $request, int $id): RedirectResponse
public function delete(int $id): RedirectResponse
```

## 短期優化 (半天內完成)

### 4. 建立 Form Request 類別
**時間**: 2 小時
**影響**: 安全性大幅提升

```bash
# 建立表單請求類別
php artisan make:request StoreWeightRequest
php artisan make:request UpdateWeightRequest
```

**StoreWeightRequest 範例**:
```php
public function rules(): array
{
    return [
        'weight' => 'required|numeric|min:20|max:300',
        'record_at' => 'required|date|before_or_equal:today',
        'note' => 'nullable|string|max:500',
        'user' => 'required|exists:users,id'
    ];
}

public function messages(): array
{
    return [
        'weight.required' => '體重為必填項目',
        'weight.numeric' => '體重必須為數字',
        'weight.min' => '體重不能少於 20 公斤',
        'weight.max' => '體重不能超過 300 公斤',
        'record_at.required' => '記錄日期為必填項目',
        'record_at.date' => '請輸入有效的日期',
        'record_at.before_or_equal' => '記錄日期不能是未來日期'
    ];
}
```

### 5. 路由標準化
**時間**: 1 小時
**影響**: 程式碼結構和維護性

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::resource('weights', WeightController::class);
    Route::get('/chart', [WeightController::class, 'show'])->name('chart');
});
```

### 6. 添加資料庫索引
**時間**: 30 分鐘
**影響**: 查詢效能

```php
// 建立遷移檔案
php artisan make:migration add_indexes_to_weights_table

// 遷移內容
Schema::table('weights', function (Blueprint $table) {
    $table->index(['user', 'record_at']);
    $table->index('record_at');
});
```

## 中期優化 (1-2 天內完成)

### 7. 前端 AJAX 優化
**時間**: 4-6 小時
**影響**: 用戶體驗大幅提升

**實現 AJAX 表單提交**:
```javascript
// 在 Blade 模板中添加
document.getElementById('weight-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 顯示成功訊息
            showNotification('記錄已成功儲存', 'success');
            // 重新載入頁面或更新表格
            location.reload();
        } else {
            // 顯示錯誤訊息
            showNotification(data.message, 'error');
        }
    });
});
```

### 8. 添加基本測試
**時間**: 3-4 小時
**影響**: 程式碼品質和維護性

```bash
# 建立測試檔案
php artisan make:test WeightControllerTest
php artisan make:test WeightModelTest
```

**WeightControllerTest 範例**:
```php
public function test_user_can_store_weight_record()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/weights', [
            'weight' => 70.5,
            'record_at' => now()->format('Y-m-d'),
            'note' => '測試記錄',
            'user' => $user->id
        ]);
    
    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('weights', [
        'user' => $user->id,
        'weight' => 70.5
    ]);
}
```

## 快速部署檢查清單

### 部署前檢查
- [ ] 所有測試通過
- [ ] 程式碼風格檢查通過
- [ ] 資料庫遷移準備就緒
- [ ] 環境變數設定正確

### 部署後驗證
- [ ] 基本功能正常運作
- [ ] 用戶認證正常
- [ ] 資料庫連線正常
- [ ] 前端資源載入正常

## 效能監控

### 關鍵指標
- 頁面載入時間 < 2 秒
- 資料庫查詢時間 < 100ms
- 記憶體使用量 < 128MB

### 監控工具
- Laravel Telescope (開發環境)
- Laravel Debugbar (開發環境)
- 生產環境日誌監控
