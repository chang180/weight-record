<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeightRequest;
use App\Http\Requests\UpdateWeightRequest;
use App\Models\Weight;
use App\Services\AchievementService;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WeightController extends Controller
{
    public function __construct(
        private AchievementService $achievementService,
        private PointsService $pointsService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user) {
            return redirect()->route('login')->with('error', '請先登入');
        }

        // 為分頁和篩選創建快取鍵
        $cacheKey = 'weights.user.' . $user->id . '.page.' . ($request->get('page', 1)) . '.filters.' . md5(serialize($request->only(['start_date', 'end_date'])));

        $weights = Cache::remember($cacheKey, 300, function () use ($user, $request) {
            $query = Weight::where('user_id', $user->id);

            // 處理日期範圍篩選
            if ($request->filled('start_date')) {
                $query->where('record_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->where('record_at', '<=', $request->end_date);
            }

            return $query->orderBy('record_at', 'DESC')
                ->paginate(15);
        });

        return view('record', compact('weights'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWeightRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            Log::error('WeightController::store - 用戶未認證或 user_id 為空', [
                'user' => $user,
                'user_id' => $user ? $user->id : null
            ]);
            return redirect()->route('login')->with('error', '請先登入');
        }

        // 確保 user_id 不為空
        $data['user_id'] = $user->id;

        // 再次檢查 user_id 是否為空
        if (empty($data['user_id'])) {
            Log::error('WeightController::store - user_id 為空', [
                'data' => $data,
                'user' => $user
            ]);
            return redirect()->route('login')->with('error', '認證錯誤，請重新登入');
        }

        $weight = Weight::create($data);

        // 清除相關快取
        $this->clearUserWeightCache($user->id);
        $user->clearWeightMilestonesCache();

        // 步驟 1：檢查未記錄天數並扣除積分
        $pointsDeducted = 0;
        $deductionReason = null;

        $recordDate = Carbon::parse($data['record_at']);
        $lastWeight = $user->weights()
            ->where('record_at', '<', $recordDate->format('Y-m-d'))
            ->latest('record_at')
            ->first();

        if ($lastWeight) {
            $lastDate = Carbon::parse($lastWeight->record_at);
            $daysDiff = $lastDate->diffInDays($recordDate);

            if ($daysDiff > 1) {
                // 有漏記天數
                $missedDays = $daysDiff - 1;
                $pointsToDeduct = $missedDays * 10;

                // 安全扣除積分（確保不會低於 0）
                $pointsDeducted = $this->pointsService->deductPointsSafely($user, $pointsToDeduct);

                if ($pointsDeducted > 0) {
                    $deductionReason = "漏記 {$missedDays} 天體重";
                }
            }
        }

        // 步驟 2：給予記錄體重獎勵（固定 20 積分）
        $this->pointsService->addPoints($user, 20, 'weight_recording');

        // 步驟 3：檢查體重里程碑成就
        $unlockedAchievements = $this->achievementService->checkWeightMilestones($user);

        // 步驟 4：檢查記錄體重成就
        $recordingAchievements = $this->achievementService->checkWeightRecordingAchievements($user);
        $unlockedAchievements = array_merge($unlockedAchievements, $recordingAchievements);

        // 準備回應訊息
        $achievementText = null;
        if (count($unlockedAchievements) > 0) {
            $achievementNames = array_map(fn($a) => $a->name, $unlockedAchievements);
            $achievementText = implode('、', $achievementNames);
        }

        $redirect = redirect()->route('dashboard')
            ->with('success', '體重記錄已成功儲存')
            ->with('recording_reward', 20);

        if ($achievementText) {
            $redirect->with('achievement', $achievementText);
        }

        if ($pointsDeducted > 0 && $deductionReason) {
            $redirect->with('points_deducted', $pointsDeducted)
                     ->with('deduction_reason', $deductionReason);
        }

        return $redirect;
    }

    /**
     * Display the specified resource.
     */
    public function show(): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            return redirect()->route('login')->with('error', '請先登入');
        }

        $cacheKey = 'chart.weights.user.' . $user->id;

        $weights = Cache::remember($cacheKey, 600, function () use ($user) {
            return Weight::where('user_id', $user->id)
                ->orderBy('record_at', 'ASC')
                ->get();
        });

        return view('chart', compact('weights'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWeightRequest $request, Weight $weight): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            Log::error('WeightController::update - 用戶未認證或 user_id 為空', [
                'user' => $user,
                'user_id' => $user ? $user->id : null
            ]);
            return redirect()->route('login')->with('error', '請先登入');
        }

        // 確保 user_id 不為空
        $data['user_id'] = $user->id;

        // 再次檢查 user_id 是否為空
        if (empty($data['user_id'])) {
            Log::error('WeightController::update - user_id 為空', [
                'data' => $data,
                'user' => $user
            ]);
            return redirect()->route('login')->with('error', '認證錯誤，請重新登入');
        }

        $weight->update($data);

        // 清除相關快取
        $this->clearUserWeightCache($user->id);

        return redirect()->route('record')
            ->with('success', '體重記錄已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Weight $weight, Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // 確認該記錄屬於當前用戶
        $user = Auth::user();
        if (!$user || $weight->user_id !== $user->id) {
            abort(403);
        }

        $user_id = $weight->user_id;
        $weight->delete();

        // 清除相關快取
        $this->clearUserWeightCache($user_id);

        return redirect()->route('record')
            ->with('success', '體重記錄已成功刪除');
    }

    /**
     * Get latest weights for API
     */
    public function latest(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            Log::error('WeightController::latest - 用戶未認證或 user_id 為空', [
                'user' => $user,
                'user_id' => $user ? $user->id : null
            ]);
            return response()->json([
                'success' => false,
                'message' => '請先登入'
            ], 401);
        }

        $cacheKey = 'latest.weights.user.' . $user->id;

        $weights = Cache::remember($cacheKey, 180, function () use ($user) {
            return Weight::where('user_id', $user->id)
                ->orderBy('record_at', 'DESC')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'hasNewData' => true,
            'weights' => $weights
        ]);
    }

    /**
     * Clear all cache related to user weights
     */
    private function clearUserWeightCache(int $user_id): void
    {
        // 清除圖表快取
        Cache::forget('chart.weights.user.' . $user_id);

        // 清除最新記錄快取
        Cache::forget('latest.weights.user.' . $user_id);

        // 清除分頁快取（使用標籤清除，如果快取驅動支援）
        if (Cache::getStore() instanceof \Illuminate\Cache\TaggedCache) {
            Cache::tags(['weights.user.' . $user_id])->flush();
        } else {
            // 如果不支援標籤，手動清除常見的分頁快取
            for ($page = 1; $page <= 10; $page++) {
                Cache::forget('weights.user.' . $user_id . '.page.' . $page . '.filters.' . md5('a:0:{}'));
            }
        }
    }

    /**
     * 匯出體重數據為 CSV
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            abort(401, '請先登入');
        }

        $weights = Weight::where('user_id', $user->id)
            ->orderBy('record_at', 'desc')
            ->get();

        $filename = 'weight_records_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function() use ($weights) {
            $file = fopen('php://output', 'w');

            // 添加 UTF-8 BOM 以支援中文
            fwrite($file, "\xEF\xBB\xBF");

            // CSV 標題行（使用英文避免編碼問題）
            fputcsv($file, ['Date', 'Weight (kg)', 'Note', 'Recorded At']);

            // 數據行
            foreach ($weights as $weight) {
                fputcsv($file, [
                    $weight->record_at->format('Y-m-d'),
                    $weight->weight,
                    $weight->note ?? '',
                    $weight->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 匯出體重數據為 PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            abort(401, '請先登入');
        }

        $weights = Weight::where('user_id', $user->id)
            ->orderBy('record_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.weight-pdf', compact('weights', 'user'));

        return $pdf->download('weight_records_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * 體重趨勢分析
     */
    public function trendAnalysis(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        try {
            $user = Auth::user();

            // 檢查用戶是否已認證
            if (!$user || !$user->id) {
                return redirect()->route('login')->with('error', '請先登入');
            }

            $days = $request->get('days', 30); // 預設分析最近30天

            $weights = Weight::where('user_id', $user->id)
                ->where('record_at', '>=', now()->subDays($days))
                ->orderBy('record_at', 'asc')
                ->get()
                ->map(function ($weight) {
                    // 確保 record_at 被正確轉換為 Carbon 實例
                    if ($weight->record_at && !($weight->record_at instanceof \Carbon\Carbon)) {
                        $weight->record_at = \Carbon\Carbon::parse($weight->record_at);
                    }
                    return $weight;
                });

            // 計算趨勢統計
            $analysis = $this->calculateTrendAnalysis($weights);

            return view('analysis.trend', compact('weights', 'analysis', 'days'));
        } catch (\Exception $e) {
            // 記錄錯誤並返回錯誤頁面
            Log::error('Trend analysis error: ' . $e->getMessage(), [
                'user_id' => Auth::user() ? Auth::user()->id : null,
                'days' => $request->get('days', 30),
                'trace' => $e->getTraceAsString()
            ]);

            return view('analysis.trend', [
                'weights' => collect(),
                'analysis' => [
                    'total_records' => 0,
                    'weight_change' => 0,
                    'average_weight' => 0,
                    'trend_direction' => 'stable',
                    'weekly_change' => 0,
                    'monthly_change' => 0,
                    'volatility' => 0,
                    'consistency_score' => 0,
                ],
                'days' => $request->get('days', 30),
                'error' => '分析過程中發生錯誤，請稍後再試'
            ]);
        }
    }

    /**
     * 計算趨勢分析數據
     */
    private function calculateTrendAnalysis($weights): array
    {
        if ($weights->count() < 2) {
            return [
                'total_records' => $weights->count(),
                'weight_change' => 0,
                'average_weight' => $weights->first()?->weight ?? 0,
                'trend_direction' => 'stable',
                'weekly_change' => 0,
                'monthly_change' => 0,
                'volatility' => 0,
                'consistency_score' => 0,
            ];
        }

        $firstWeight = $weights->first()->weight;
        $lastWeight = $weights->last()->weight;
        $weightChange = $lastWeight - $firstWeight;

        // 計算平均體重
        $averageWeight = $weights->avg('weight');

        // 計算趨勢方向
        $trendDirection = 'stable';
        if ($weightChange > 0.5) {
            $trendDirection = 'increasing';
        } elseif ($weightChange < -0.5) {
            $trendDirection = 'decreasing';
        }

        // 計算週變化
        $weeklyChange = 0;
        if ($weights->count() >= 7) {
            $weekAgo = $weights->where('record_at', '>=', now()->subDays(7))->first()?->weight ?? $firstWeight;
            $weeklyChange = $lastWeight - $weekAgo;
        }

        // 計算月變化
        $monthlyChange = 0;
        if ($weights->count() >= 30) {
            $monthAgo = $weights->where('record_at', '>=', now()->subDays(30))->first()?->weight ?? $firstWeight;
            $monthlyChange = $lastWeight - $monthAgo;
        }

        // 計算波動性（標準差）
        $weightsArray = $weights->pluck('weight')->toArray();
        $volatility = $this->calculateStandardDeviation($weightsArray);

        // 計算一致性分數（基於記錄頻率）
        $totalDays = 1;
        if ($weights->count() > 0) {
            $firstWeight = $weights->first();
            $lastWeight = $weights->last();
            if ($firstWeight && $lastWeight && $firstWeight->record_at && $lastWeight->record_at) {
                // 確保 record_at 是 Carbon 實例
                $firstDate = $firstWeight->record_at instanceof \Carbon\Carbon
                    ? $firstWeight->record_at
                    : \Carbon\Carbon::parse($firstWeight->record_at);
                $lastDate = $lastWeight->record_at instanceof \Carbon\Carbon
                    ? $lastWeight->record_at
                    : \Carbon\Carbon::parse($lastWeight->record_at);

                $totalDays = $firstDate->diffInDays($lastDate) + 1;
            }
        }
        $consistencyScore = min(100, ($weights->count() / $totalDays) * 100);

        return [
            'total_records' => $weights->count(),
            'weight_change' => round($weightChange, 1),
            'average_weight' => round($averageWeight, 1),
            'trend_direction' => $trendDirection,
            'weekly_change' => round($weeklyChange, 1),
            'monthly_change' => round($monthlyChange, 1),
            'volatility' => round($volatility, 1),
            'consistency_score' => round($consistencyScore, 1),
        ];
    }

    /**
     * 計算標準差
     */
    private function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) return 0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / $count;

        return sqrt($variance);
    }

    /**
     * 健康指標計算
     */
    public function healthMetrics(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // 檢查用戶是否已認證
        if (!$user || !$user->id) {
            return redirect()->route('login')->with('error', '請先登入');
        }

        // 獲取最新體重記錄
        $latestWeight = Weight::where('user_id', $user->id)
            ->orderBy('record_at', 'desc')
            ->first();

        if (!$latestWeight) {
            return view('analysis.health', [
                'hasData' => false,
                'message' => '請先記錄體重數據以查看健康指標'
            ]);
        }

        // 計算健康指標
        $metrics = $this->calculateHealthMetrics($latestWeight->weight);

        // 獲取用戶的活躍目標
        $activeGoal = Auth::user()->activeWeightGoal;

        return view('analysis.health', compact('metrics', 'activeGoal', 'latestWeight'));
    }

    /**
     * 計算健康指標
     */
    private function calculateHealthMetrics(float $weight): array
    {
        // 從用戶資料獲取身高，如果未設定則使用預設值
        $height = Auth::user()->height ?? 170; // cm
        $heightInMeters = $height / 100;

        // 計算 BMI
        $bmi = $weight / ($heightInMeters * $heightInMeters);

        // BMI 分類
        $bmiCategory = $this->getBmiCategory($bmi);

        // 理想體重範圍
        $idealWeightRange = $this->getIdealWeightRange($height);

        // 健康建議
        $healthAdvice = $this->getHealthAdvice($bmi, $bmiCategory);

        return [
            'weight' => $weight,
            'height' => $height,
            'bmi' => round($bmi, 1),
            'bmi_category' => $bmiCategory,
            'ideal_weight_min' => $idealWeightRange['min'],
            'ideal_weight_max' => $idealWeightRange['max'],
            'health_advice' => $healthAdvice,
        ];
    }

    /**
     * 獲取 BMI 分類
     */
    private function getBmiCategory(float $bmi): array
    {
        if ($bmi < 18.5) {
            return [
                'name' => '體重過輕',
                'color' => 'blue',
                'icon' => '📉',
                'description' => '您的體重低於正常範圍'
            ];
        } elseif ($bmi < 24) {
            return [
                'name' => '正常體重',
                'color' => 'green',
                'icon' => '✅',
                'description' => '您的體重在健康範圍內'
            ];
        } elseif ($bmi < 27) {
            return [
                'name' => '體重過重',
                'color' => 'yellow',
                'icon' => '⚠️',
                'description' => '您的體重略高於正常範圍'
            ];
        } else {
            return [
                'name' => '肥胖',
                'color' => 'red',
                'icon' => '🚨',
                'description' => '您的體重明顯高於正常範圍'
            ];
        }
    }

    /**
     * 獲取理想體重範圍
     */
    private function getIdealWeightRange(int $height): array
    {
        $heightInMeters = $height / 100;
        $minBmi = 18.5;
        $maxBmi = 24;

        return [
            'min' => round($minBmi * $heightInMeters * $heightInMeters, 1),
            'max' => round($maxBmi * $heightInMeters * $heightInMeters, 1),
        ];
    }

    /**
     * 獲取健康建議
     */
    private function getHealthAdvice(float $bmi, array $category): array
    {
        $advice = [];

        if ($bmi < 18.5) {
            $advice = [
                '飲食建議' => '增加營養攝取，多吃高蛋白食物',
                '運動建議' => '進行適度的力量訓練，增加肌肉量',
                '生活建議' => '保持規律作息，避免過度節食'
            ];
        } elseif ($bmi < 24) {
            $advice = [
                '飲食建議' => '保持均衡飲食，多吃蔬果',
                '運動建議' => '維持規律運動，每週至少150分鐘',
                '生活建議' => '保持良好作息，定期監測體重'
            ];
        } elseif ($bmi < 27) {
            $advice = [
                '飲食建議' => '控制熱量攝取，減少高熱量食物',
                '運動建議' => '增加有氧運動，每週至少200分鐘',
                '生活建議' => '建立健康生活習慣，避免久坐'
            ];
        } else {
            $advice = [
                '飲食建議' => '嚴格控制飲食，尋求專業營養師建議',
                '運動建議' => '循序漸進增加運動量，避免過度運動',
                '生活建議' => '建議諮詢醫生，制定減重計劃'
            ];
        }

        return $advice;
    }
}
