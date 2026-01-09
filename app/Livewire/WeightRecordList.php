<?php

namespace App\Livewire;

use App\Models\Weight;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class WeightRecordList extends Component
{
    use WithPagination;

    public $start_date = '';
    public $end_date = '';
    public $editingId = null;
    public $editingWeight = '';
    public $editingNote = '';
    public $editingRecordAt = '';
    public $deletingId = null;
    public $showDeleteModal = false;

    protected PointsService $pointsService;

    public function boot(PointsService $pointsService): void
    {
        $this->pointsService = $pointsService;
    }

    public function mount(): void
    {
        $this->start_date = request('start_date', '');
        $this->end_date = request('end_date', '');
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function edit($id): void
    {
        $weight = Weight::where('user_id', Auth::id())->find($id);
        
        if (!$weight) {
            return; // 權限不足，靜默失敗
        }
        
        $this->editingId = $id;
        $this->editingWeight = $weight->weight;
        $this->editingNote = $weight->note ?? '';
        $this->editingRecordAt = $weight->record_at->format('Y-m-d');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingWeight', 'editingNote', 'editingRecordAt']);
    }

    public function update(): void
    {
        $this->validate([
            'editingWeight' => 'required|numeric|min:20|max:300',
            'editingRecordAt' => 'required|date|before_or_equal:today',
            'editingNote' => 'nullable|string|max:500',
        ], [
            'editingWeight.required' => '體重為必填項目',
            'editingWeight.numeric' => '體重必須為數字',
            'editingWeight.min' => '體重不能少於 20 公斤',
            'editingWeight.max' => '體重不能超過 300 公斤',
            'editingRecordAt.required' => '記錄日期為必填項目',
            'editingRecordAt.date' => '請輸入有效的日期',
            'editingRecordAt.before_or_equal' => '記錄日期不能是未來日期',
            'editingNote.max' => '備註不能超過 500 個字元',
        ]);

        $weight = Weight::where('user_id', Auth::id())->find($this->editingId);
        
        if (!$weight) {
            $this->addError('editingId', '找不到該記錄或無權限');
            return;
        }

        // 檢查日期是否與其他記錄衝突（排除自己）
        $existingWeight = Weight::where('user_id', Auth::id())
            ->where('id', '!=', $weight->id)
            ->whereDate('record_at', Carbon::parse($this->editingRecordAt)->format('Y-m-d'))
            ->first();

        if ($existingWeight) {
            $this->addError('editingRecordAt', '該日期已經記錄過體重了，請選擇其他日期。');
            return;
        }

        $weight->update([
            'weight' => $this->editingWeight,
            'record_at' => $this->editingRecordAt,
            'note' => !empty($this->editingNote) ? $this->editingNote : null,
        ]);

        // 清除相關快取
        $this->clearUserWeightCache(Auth::id());

        $this->cancelEdit();
        
        $this->dispatch('weight-updated');
    }

    public function confirmDelete($id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->reset(['deletingId', 'showDeleteModal']);
    }

    public function delete(): void
    {
        $weight = Weight::where('user_id', Auth::id())->find($this->deletingId);
        
        if (!$weight) {
            $this->cancelDelete();
            return; // 權限不足，靜默失敗
        }
        
        $user = Auth::user();

        // 檢查是否為今天的記錄，如果是則扣除獎勵積分（防刷分）
        $recordDate = Carbon::parse($weight->record_at);
        if ($recordDate->isToday() && $weight->created_at->isToday()) {
            // 扣除 20 積分（如果之前有給獎勵）
            $this->pointsService->deductPointsSafely($user, 20);
        }

        $user_id = $weight->user_id;
        $weight->delete();

        // 清除相關快取
        $this->clearUserWeightCache($user_id);

        $this->cancelDelete();
        $this->resetPage();
        
        $this->dispatch('weight-deleted');
    }

    public function resetFilters(): void
    {
        $this->start_date = '';
        $this->end_date = '';
        $this->resetPage();
    }

    private function clearUserWeightCache(int $user_id): void
    {
        // 清除圖表快取
        Cache::forget('chart.weights.user.' . $user_id);

        // 清除最新記錄快取
        Cache::forget('latest.weights.user.' . $user_id);

        // 清除分頁快取
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget('weights.user.' . $user_id . '.page.' . $page . '.filters.' . md5('a:0:{}'));
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Weight::where('user_id', $user->id);

        // 處理日期範圍篩選
        if ($this->start_date) {
            $query->whereDate('record_at', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('record_at', '<=', $this->end_date);
        }

        $weights = $query->orderBy('record_at', 'DESC')
            ->paginate(15);

        return view('livewire.weight-record-list', [
            'weights' => $weights,
        ]);
    }
}
