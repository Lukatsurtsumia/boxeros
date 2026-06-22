<div class="space-y-4 pb-4">

    {{-- Page title --}}
    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">Daily Log</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-primary' }} text-xs px-3 py-1.5">
            {{ $showForm ? 'Cancel' : '+ Log Today' }}
        </button>
    </div>

    {{-- Log form --}}
    @if($showForm)
    <form wire:submit="saveLog" class="space-y-3">

        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Body Stats</div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Weight (kg)</label>
                    <input type="number" step="0.1" wire:model="weight_kg" class="input-dark" placeholder="75.5">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Calories</label>
                    <input type="number" wire:model="calories_consumed" class="input-dark" placeholder="2400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Sleep (hrs)</label>
                    <input type="number" wire:model="sleep_hours" class="input-dark" placeholder="8" min="0" max="24">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Training (min)</label>
                    <input type="number" wire:model="training_minutes" class="input-dark" placeholder="90">
                </div>
            </div>
        </div>

        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Mood</div>
            <div class="flex justify-around py-1">
                @foreach(['great' => ['🔥','Great'], 'good' => ['💪','Good'], 'okay' => ['😐','Okay'], 'tired' => ['😴','Tired'], 'bad' => ['🤕','Rough']] as $m => [$emoji, $label])
                <button type="button" wire:click="$set('mood', '{{ $m }}')"
                        class="mood-btn flex flex-col items-center gap-1 {{ $mood === $m ? 'selected' : '' }}">
                    <span>{{ $emoji }}</span>
                    <span class="text-xs" style="color: var(--text-muted); font-size: 0.6rem;">{{ $label }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <div class="card space-y-3">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Energy Level</span>
                <span class="font-display text-lg font-bold" style="color: var(--blood);">{{ $energy_level }}/10</span>
            </div>
            <input type="range" min="1" max="10" wire:model="energy_level" class="w-full" style="accent-color: var(--blood);">
        </div>

        <div class="card">
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Notes</label>
            <textarea wire:model="notes" rows="3" class="input-dark" placeholder="How was training? Any highlights?"></textarea>
        </div>

        <button type="submit" class="btn-primary w-full py-3">Save Log</button>
    </form>
    @endif

    {{-- Water tracker --}}
    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-semibold uppercase tracking-wider" style="color: #3498db;">Water Today</div>
            <div class="font-display text-2xl font-bold" style="color: #3498db;">{{ number_format($water_liters, 1) }} L</div>
        </div>
        <div class="progress-track mb-3">
            <div class="progress-fill progress-blue" style="width: {{ $waterPct }}%"></div>
        </div>
        <div class="text-xs text-center mb-3" style="color: var(--text-muted);">{{ $waterPct }}% of {{ $waterGoal }}L goal</div>
        <div class="grid grid-cols-4 gap-2">
            <button type="button" wire:click="addWater(0.25)" class="water-btn">+250ml</button>
            <button type="button" wire:click="addWater(0.5)"  class="water-btn">+500ml</button>
            <button type="button" wire:click="addWater(0.75)" class="water-btn">+750ml</button>
            <button type="button" wire:click="addWater(1)"    class="water-btn">+1L</button>
        </div>
    </div>

    {{-- Log history --}}
    @if($logs->count() > 0)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Recent Logs</div>
        <div class="space-y-1">
            @foreach($logs as $log)
            <div class="flex items-center py-2.5" style="border-bottom: 1px solid var(--dark-border);">
                <div class="w-16 flex-shrink-0">
                    <div class="text-xs font-semibold">{{ $log->log_date->format('M d') }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ $log->log_date->format('D') }}</div>
                </div>
                <div class="flex-1 grid grid-cols-4 gap-2 text-center">
                    <div>
                        <div class="text-sm font-bold">{{ $log->weight_kg ?? '--' }}</div>
                        <div class="text-xs" style="color: var(--text-muted);">kg</div>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color: #3498db;">{{ number_format($log->water_liters, 1) }}</div>
                        <div class="text-xs" style="color: var(--text-muted);">L</div>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color: var(--gold);">{{ $log->calories_consumed ?? '--' }}</div>
                        <div class="text-xs" style="color: var(--text-muted);">kcal</div>
                    </div>
                    <div class="text-xl">
                        {{ ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'][$log->mood ?? 'good'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
