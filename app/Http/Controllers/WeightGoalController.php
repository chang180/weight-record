<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeightGoalRequest;
use App\Http\Requests\UpdateWeightGoalRequest;
use App\Models\WeightGoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeightGoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $goals = auth()->user()->weightGoals()->orderBy('created_at', 'desc')->paginate(10);
        
        return view('goals.index', compact('goals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('goals.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWeightGoalRequest $request): RedirectResponse
    {
        // 停用其他活躍目標
        auth()->user()->weightGoals()->update(['is_active' => false]);
        
        // 創建新目標
        auth()->user()->weightGoals()->create($request->validated());

        return redirect()->route('goals.index')
            ->with('success', '體重目標設定成功！');
    }

    /**
     * Display the specified resource.
     */
    public function show(WeightGoal $goal): View
    {
        $this->authorize('view', $goal);
        
        return view('goals.show', compact('goal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeightGoal $goal): View
    {
        $this->authorize('update', $goal);
        
        return view('goals.edit', compact('goal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWeightGoalRequest $request, WeightGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        $goal->update($request->validated());

        return redirect()->route('goals.index')
            ->with('success', '體重目標更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeightGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        
        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', '體重目標已刪除！');
    }

    /**
     * 設定目標為活躍狀態
     */
    public function activate(WeightGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        // 停用其他活躍目標
        auth()->user()->weightGoals()->update(['is_active' => false]);
        
        // 啟用選定目標
        $goal->update(['is_active' => true]);

        return redirect()->route('goals.index')
            ->with('success', '目標已設為活躍狀態！');
    }
}
