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
        <div class="col-span-2">
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">What did you eat?</label>
            <input type="text" wire:model="name" class="input-dark" placeholder="Chicken & rice, pasta, salad... (leave blank if uploading photo)">
        </div>
        <div class="grid grid-cols-2 gap-2">
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
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Time</label>
                <input type="time" wire:model="eaten_time" class="input-dark">
            </div>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Details (optional)</label>
            <input type="text" wire:model="description" class="input-dark" placeholder="Big portion, home-cooked, restaurant name...">
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Photo — CORNER will identify & estimate calories from it</label>
            <input type="file" wire:model="photo" class="input-dark text-xs" accept="image/*" capture="environment">
            @error('photo') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>
        <p class="text-xs py-1 px-2 rounded-lg" style="color: var(--text-muted); background: rgba(255,255,255,0.03);">
            No need to count calories — CORNER estimates them automatically.
        </p>
        <button type="submit" class="btn-gold w-full py-2.5" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Log Meal</span>
            <span wire:loading wire:target="save">Estimating calories...</span>
        </button>
    </form>
    @endif

    {{-- Calorie confirmation --}}
    @if($meals->count() > 0 && !$confirmedToday)
    <div class="card card-gold">
        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--gold);">End of Day — Calories Check</div>
        @if($estimatedTotal > 0)
        <p class="text-sm mb-3 leading-relaxed">
            Based on what you logged, your day looks like about
            <strong style="color: var(--gold); font-size: 1.1em;">{{ number_format($estimatedTotal) }} kcal</strong>.
            Does that sound right?
        </p>
        <div class="grid grid-cols-3 gap-2">
            <button wire:click="confirmCalories('less')" class="btn-ghost text-xs py-2.5">Less than that</button>
            <button wire:click="confirmCalories('right')" class="btn-gold text-xs py-2.5">About right ✓</button>
            <button wire:click="confirmCalories('more')" class="btn-ghost text-xs py-2.5">More than that</button>
        </div>
        @else
        <p class="text-sm" style="color: var(--text-muted);">Estimating calories for your meals... Add an API key to enable this.</p>
        @endif
    </div>
    @elseif($confirmedToday)
    <div class="card" style="border-color: rgba(46,204,113,0.3);">
        <div class="flex items-center justify-between">
            <span class="text-xs" style="color: var(--text-muted);">Today's calories confirmed</span>
            <span class="font-display text-2xl font-bold" style="color: #2ecc71;">~{{ number_format($confirmedToday) }} kcal</span>
        </div>
    </div>
    @endif

    {{-- Meals list --}}
    <div class="space-y-2">
        @forelse($meals as $meal)
        <div wire:key="meal-{{ $meal->id }}" class="card flex items-center gap-3">
            @if($meal->photo)
            <img src="{{ Storage::url($meal->photo) }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
            @else
            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                {{ ['breakfast'=>'🌅','lunch'=>'☀️','dinner'=>'🌙','snack'=>'🍎','pre-workout'=>'⚡','post-workout'=>'💪'][$meal->meal_type] ?? '🍽️' }}
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm truncate">{{ $meal->name }}</div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs" style="color: var(--text-muted);">{{ ucwords(str_replace('-', ' ', $meal->meal_type)) }}</span>
                    @if($meal->eaten_time)
                    <span class="text-xs" style="color: var(--text-muted);">· {{ $meal->eaten_time }}</span>
                    @endif
                </div>
                <div class="mt-0.5">
                    @if($meal->calories)
                    <span class="text-xs" style="color: {{ $meal->calories_source === 'confirmed' ? '#2ecc71' : 'var(--gold)' }};">
                        ~{{ $meal->calories }} kcal
                    </span>
                    @if($meal->calories_source === 'ai_estimated')
                    <span class="text-xs" style="color: var(--text-muted);"> AI est.</span>
                    @endif
                    @else
                    <span class="text-xs" style="color: var(--text-muted);">estimating...</span>
                    @endif
                </div>
            </div>
            <button type="button" wire:click="delete({{ $meal->id }})" wire:confirm="Delete this meal?" title="Delete meal" class="text-xs px-2 py-1 rounded-lg flex-shrink-0" style="color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
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
