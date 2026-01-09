<div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transition-all duration-300 hover:shadow-xl"
         x-data="{ 
             points: {{ $availablePoints }},
             animate: false 
         }"
         x-init="$watch('points', () => { animate = true; setTimeout(() => animate = false, 500); })"
         @points-updated.window="points = $event.detail.points; animate = true; setTimeout(() => animate = false, 500);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">可用積分</p>
                <p class="text-3xl font-bold transition-all duration-300"
                   :class="animate ? 'scale-125' : ''"
                   x-text="points">{{ $availablePoints }}</p>
                <p class="text-purple-200 text-xs mt-1">總積分：{{ $totalPoints }}</p>
            </div>
            <div class="text-4xl transition-transform duration-300 hover:rotate-12">💎</div>
        </div>
    </div>
</div>
