<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeightRequest;
use App\Http\Requests\UpdateWeightRequest;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user_id = auth()->id();

        // 為分頁和篩選創建快取鍵
        $cacheKey = 'weights.user.' . $user_id . '.page.' . ($request->get('page', 1)) . '.filters.' . md5(serialize($request->only(['start_date', 'end_date'])));

        $weights = Cache::remember($cacheKey, 300, function () use ($user_id, $request) {
            $query = Weight::where('user_id', $user_id);

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
        $data['user_id'] = auth()->id();

        $weight = Weight::create($data);

        // 清除相關快取
        $this->clearUserWeightCache($data['user_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '體重記錄已成功儲存',
                'data' => $weight
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', '體重記錄已成功儲存');
    }

    /**
     * Display the specified resource.
     */
    public function show(): View
    {
        $user_id = auth()->id();

        $cacheKey = 'chart.weights.user.' . $user_id;

        $weights = Cache::remember($cacheKey, 600, function () use ($user_id) {
            return Weight::where('user_id', $user_id)
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
        $data['user_id'] = auth()->id();

        $weight->update($data);

        // 清除相關快取
        $this->clearUserWeightCache($data['user_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '體重記錄已成功更新',
                'data' => $weight->fresh()
            ]);
        }

        return redirect()->route('record')
            ->with('success', '體重記錄已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Weight $weight, Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // 確認該記錄屬於當前用戶
        if ($weight->user_id !== auth()->id()) {
            abort(403);
        }

        $user_id = $weight->user_id;
        $weight->delete();

        // 清除相關快取
        $this->clearUserWeightCache($user_id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '體重記錄已成功刪除'
            ]);
        }

        return redirect()->route('record')
            ->with('success', '體重記錄已成功刪除');
    }

    /**
     * Get latest weights for API
     */
    public function latest(): \Illuminate\Http\JsonResponse
    {
        $user_id = auth()->id();

        $cacheKey = 'latest.weights.user.' . $user_id;

        $weights = Cache::remember($cacheKey, 180, function () use ($user_id) {
            return Weight::where('user_id', $user_id)
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
}
