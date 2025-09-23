<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeightRequest;
use App\Http\Requests\UpdateWeightRequest;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user_id = auth()->id();
        $query = Weight::where('user_id', $user_id);

        // 處理日期範圍篩選
        if ($request->filled('start_date')) {
            $query->where('record_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('record_at', '<=', $request->end_date);
        }

        $weights = $query->orderBy('record_at', 'DESC')
            ->paginate(15);

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
    public function store(StoreWeightRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        Weight::create($data);

        return redirect()->route('dashboard')
            ->with('success', '體重記錄已成功儲存');
    }

    /**
     * Display the specified resource.
     */
    public function show(): View
    {
        $user_id = auth()->id();
        $weights = Weight::where('user_id', $user_id)
            ->orderBy('record_at', 'ASC')
            ->get();

        return view('chart', compact('weights'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWeightRequest $request, Weight $weight): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $weight->update($data);

        return redirect()->route('record')
            ->with('success', '體重記錄已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Weight $weight): RedirectResponse
    {
        // 確認該記錄屬於當前用戶
        if ($weight->user_id !== auth()->id()) {
            abort(403);
        }

        $weight->delete();

        return redirect()->route('record')
            ->with('success', '體重記錄已成功刪除');
    }
}
