<div class="space-y-4 pb-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">Nutrition</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-primary' }} text-xs px-3 py-1.5">
            {{ $showForm ? 'Cancel' : '+ Add Meal' }}
        </button>
    </div>

    {{-- Add meal form --}}
    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div class="grid grid-cols-2 gap-2">
            <div class="col-span-2">
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Meal Name</label>
                <input type="text" wire:model="name" class="input-dark" placeholder="Chicken & rice" required>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Type</label>
                <select wire:model="meal_type" class="input-dark">
                    <option value="breakfast">Breakfast</option>
                    <option value="pre-workout">Pre-workout</option>
                    <option value="lunch">Lunch</option>
                    <option value="post-workout">Post-workout</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                </select>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Calories</label>
                <input type="number" wire:model="calories" class="input-dark" placeholder="450">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Protein (g)</label>
                <input type="number" step="0.1" wire:model="protein_g" class="input-dark" placeholder="35">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Carbs (g)</label>
                <input type="number" step="0.1" wire:model="carbs_g" class="input-dark" placeholder="50">
            </div>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Notes</label>
            <input type="text" wire:model="description" class="input-dark" placeholder="Optional notes">
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Photo (optional)</label>
            <input type="file" wire:model="photo" class="input-dark text-xs" accept="image/*">
        </div>
        <button type="submit" class="btn-gold w-full py-2.5">Log Meal</button>
    </form>
    @endif

    {{-- Daily totals --}}
    @if($meals->count() > 0)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Today's Totals</div>
        <div class="grid grid-cols-4 gap-2 text-center">
            <div>
                <div class="font-display text-xl font-bold" style="color: var(--gold);">{{ number_format($totalCalories) }}</div>
                <div class="text-xs" style="color: var(--text-muted);">kcal</div>
            </div>
            <div>
                <div class="font-display text-xl font-bold" style="color: #e74c3c;">{{ number_format($totalProtein, 0) }}g</div>
                <div class="text-xs" style="color: var(--text-muted);">protein</div>
            </div>
            <div>
                <div class="font-display text-xl font-bold" style="color: #f39c12;">{{ number_format($totalCarbs, 0) }}g</div>
                <div class="text-xs" style="color: var(--text-muted);">carbs</div>
            </div>
            <div>
                <div class="font-display text-xl font-bold" style="color: #9b59b6;">{{ number_format($totalFat, 0) }}g</div>
                <div class="text-xs" style="color: var(--text-muted);">fat</div>
            </div>
        </div>

        {{-- Macro bar --}}
        @if($totalProtein + $totalCarbs + $totalFat > 0)
        @php
            $macroTotal = ($totalProtein * 4) + ($totalCarbs * 4) + ($totalFat * 9);
            $proteinPct = $macroTotal > 0 ? round($totalProtein * 4 / $macroTotal * 100) : 0;
            $carbsPct   = $macroTotal > 0 ? round($totalCarbs * 4 / $macroTotal * 100) : 0;
            $fatPct     = $macroTotal > 0 ? round($totalFat * 9 / $macroTotal * 100) : 0;
        @endphp
        <div class="flex rounded-full overflow-hidden mt-3" style="height: 8px;">
            <div style="width: {{ $proteinPct }}%; background: #e74c3c;"></div>
            <div style="width: {{ $carbsPct }}%; background: #f39c12;"></div>
            <div style="width: {{ $fatPct }}%; background: #9b59b6;"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- Meals list --}}
    <div class="space-y-2">
        @forelse($meals as $meal)
        <div class="card flex items-center gap-3">
            @if($meal->photo)
            <img src="{{ Storage::url($meal->photo) }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
            @else
            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                {{ ['breakfast'=>'🌅','lunch'=>'☀️','dinner'=>'🌙','snack'=>'🍎','pre-workout'=>'⚡','post-workout'=>'💪'][$meal->meal_type] ?? '🍽️' }}
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm truncate">{{ $meal->name }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ ucwords(str_replace('-', ' ', $meal->meal_type)) }}</div>
                <div class="flex gap-2 mt-1">
                    @if($meal->calories) <span class="text-xs" style="color: var(--gold);">{{ $meal->calories }} kcal</span> @endif
                    @if($meal->protein_g) <span class="text-xs" style="color: #e74c3c;">{{ $meal->protein_g }}g P</span> @endif
                </div>
            </div>
            <button wire:click="delete({{ $meal->id }})" class="text-xs px-2 py-1 rounded-lg" style="color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
        </div>
        @empty
        <div class="card text-center py-8">
            <div class="text-3xl mb-2">🍽️</div>
            <div class="text-sm" style="color: var(--text-muted);">No meals logged today</div>
            <button wire:click="$set('showForm', true)" class="btn-primary mt-3 text-sm">Log First Meal</button>
        </div>
        @endforelse
    </div>

</div>
