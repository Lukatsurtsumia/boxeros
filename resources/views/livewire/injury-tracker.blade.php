<div class="space-y-4 pb-4">

    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">Injuries</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-primary' }} text-xs px-3 py-1.5">
            {{ $showForm ? 'Cancel' : '+ Report Injury' }}
        </button>
    </div>

    @if($active->count() > 0)
    <div class="card" style="border-color: rgba(192,57,43,0.3);">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full" style="background: var(--blood); animation: pulse 1.5s infinite;"></span>
            <span class="text-xs font-semibold uppercase tracking-wider" style="color: var(--blood);">Active Injuries ({{ $active->count() }})</span>
        </div>
    </div>
    @endif

    {{-- Report form --}}
    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Report an Injury</div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Body Part</label>
                <input type="text" wire:model="body_part" class="input-dark" placeholder="Left shoulder" required>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Severity</label>
                <select wire:model="severity" class="input-dark">
                    <option value="minor">Minor</option>
                    <option value="moderate">Moderate</option>
                    <option value="serious">Serious</option>
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Title</label>
            <input type="text" wire:model="title" class="input-dark" placeholder="Rotator cuff strain" required>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Describe what happened</label>
            <textarea wire:model="description" rows="3" class="input-dark" placeholder="Felt a sharp pain during sparring when throwing a hook..." required></textarea>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Date</label>
                <input type="date" wire:model="injury_date" class="input-dark">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Est. Recovery</label>
                <input type="date" wire:model="expected_recovery" class="input-dark">
            </div>
        </div>
        <button type="submit" class="btn-primary w-full py-2.5">
            <span wire:loading.remove>Report & Get AI Feedback</span>
            <span wire:loading>Getting advice from coach...</span>
        </button>
        <p class="text-xs text-center" style="color: var(--text-muted);">Your AI coach will analyze this and give recovery advice</p>
    </form>
    @endif

    {{-- Active injuries --}}
    @foreach($active as $injury)
    <div class="card" style="border-color: rgba(192,57,43,0.25);">
        <div class="flex items-start justify-between mb-2">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="sev-{{ $injury->severity }} font-semibold text-sm">{{ ucfirst($injury->severity) }}</span>
                    <span class="badge badge-red">Active</span>
                </div>
                <div class="font-display text-lg font-bold">{{ $injury->title }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $injury->body_part }} · {{ $injury->injury_date->format('M d, Y') }}</div>
            </div>
            <div class="flex flex-col gap-1">
                <button wire:click="updateStatus({{ $injury->id }}, 'recovering')" class="btn-ghost text-xs px-2 py-1">Recovering</button>
                <button wire:click="updateStatus({{ $injury->id }}, 'healed')" class="btn-ghost text-xs px-2 py-1" style="color: #2ecc71;">Healed ✓</button>
            </div>
        </div>
        <p class="text-sm mb-3 leading-relaxed" style="color: var(--text-muted);">{{ $injury->description }}</p>
        @if($injury->ai_feedback)
        <div class="rounded-xl p-3" style="background: rgba(243,156,18,0.06); border: 1px solid rgba(243,156,18,0.2);">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm">🤖</span>
                <span class="text-xs font-semibold" style="color: var(--gold);">Coach AI Advice</span>
            </div>
            <p class="text-sm leading-relaxed" style="color: var(--text-muted);">{{ $injury->ai_feedback }}</p>
        </div>
        @else
        <div class="text-xs text-center py-2" style="color: var(--text-muted);">
            Add ANTHROPIC_API_KEY to .env for AI coaching feedback
        </div>
        @endif
    </div>
    @endforeach

    {{-- Recovering --}}
    @foreach($recovering as $injury)
    <div class="card" style="border-color: rgba(243,156,18,0.2);">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="badge badge-gold">Recovering</span>
                </div>
                <div class="font-semibold">{{ $injury->title }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $injury->body_part }}</div>
            </div>
            <button wire:click="updateStatus({{ $injury->id }}, 'healed')" class="btn-ghost text-xs" style="color: #2ecc71;">Mark Healed</button>
        </div>
        @if($injury->expected_recovery)
        <div class="mt-2 text-xs" style="color: var(--text-muted);">Expected recovery: {{ $injury->expected_recovery->format('M d, Y') }}</div>
        @endif
    </div>
    @endforeach

    {{-- Healed --}}
    @if($healed->count() > 0)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Healed ({{ $healed->count() }})</div>
        @foreach($healed as $injury)
        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid var(--dark-border);">
            <div>
                <div class="text-sm font-medium line-through" style="color: var(--text-muted);">{{ $injury->title }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $injury->body_part }}</div>
            </div>
            <span class="badge badge-green">Healed</span>
        </div>
        @endforeach
    </div>
    @endif

    @if($injuries->count() === 0 && !$showForm)
    <div class="card text-center py-10">
        <div class="text-4xl mb-3">💪</div>
        <div class="font-display text-xl font-bold mb-2">No Injuries</div>
        <p class="text-sm" style="color: var(--text-muted);">Stay safe, train smart.</p>
    </div>
    @endif

</div>
