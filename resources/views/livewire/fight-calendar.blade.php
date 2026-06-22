<div class="space-y-4 pb-4">

    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">Fights</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-gold' }} text-xs px-3 py-1.5">
            {{ $showForm ? 'Cancel' : '+ Add Fight' }}
        </button>
    </div>

    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div class="grid grid-cols-2 gap-2">
            <div class="col-span-2">
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Opponent</label>
                <input type="text" wire:model="opponent_name" class="input-dark" placeholder="John Doe" required>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Fight Date</label>
                <input type="datetime-local" wire:model="fight_date" class="input-dark">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Rounds</label>
                <input type="number" wire:model="rounds" class="input-dark" min="1" max="15">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Event</label>
                <input type="text" wire:model="event_name" class="input-dark" placeholder="Event name">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Location</label>
                <input type="text" wire:model="location" class="input-dark" placeholder="City, Country">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Weight Class</label>
                <input type="text" wire:model="weight_class" class="input-dark" placeholder="Lightweight">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Result</label>
                <select wire:model="result" class="input-dark">
                    <option value="upcoming">Upcoming</option>
                    <option value="win">Win</option>
                    <option value="loss">Loss</option>
                    <option value="draw">Draw</option>
                    <option value="no_contest">No Contest</option>
                </select>
            </div>
            @if($result !== 'upcoming')
            <div class="col-span-2">
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Method (KO, TKO, UD...)</label>
                <input type="text" wire:model="result_method" class="input-dark" placeholder="KO R3">
            </div>
            @endif
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Notes</label>
            <textarea wire:model="notes" rows="2" class="input-dark" placeholder="Fight notes..."></textarea>
        </div>
        <button type="submit" class="btn-gold w-full py-2.5">Save Fight</button>
    </form>
    @endif

    {{-- Upcoming fights --}}
    @if($upcoming->count() > 0)
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Upcoming</div>
        @foreach($upcoming as $fight)
        @php $days = max(0, now()->diffInDays($fight->fight_date, false)); @endphp
        <div class="card card-gold mb-3">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-semibold mb-1" style="color: var(--gold);">{{ $fight->fight_date->format('D, M d · H:i') }}</div>
                    <div class="font-display text-2xl font-bold">vs {{ $fight->opponent_name }}</div>
                    @if($fight->event_name)
                    <div class="text-sm mt-0.5" style="color: var(--text-muted);">{{ $fight->event_name }}</div>
                    @endif
                    @if($fight->location)
                    <div class="text-xs mt-1" style="color: var(--text-muted);">📍 {{ $fight->location }}</div>
                    @endif
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    <div class="font-display text-3xl font-bold" style="color: var(--gold);">{{ $days }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">days</div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
                @if($fight->weight_class) <span class="badge badge-gray">{{ $fight->weight_class }}</span> @endif
                <span class="badge badge-gray">{{ $fight->rounds }}R</span>
                <button wire:click="delete({{ $fight->id }})" class="ml-auto text-xs" style="color: var(--text-muted);">Remove</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Fight history --}}
    @if($history->count() > 0)
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">History</div>
        <div class="card">
            @foreach($history as $fight)
            @php $rc = ['win'=>'badge-green','loss'=>'badge-red','draw'=>'badge-gray','no_contest'=>'badge-gray'][$fight->result] ?? 'badge-gray' @endphp
            <div class="flex items-center py-3" style="border-bottom: 1px solid var(--dark-border);">
                <div class="flex-1">
                    <div class="font-medium text-sm">vs {{ $fight->opponent_name }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">
                        {{ $fight->fight_date->format('M d, Y') }}
                        @if($fight->result_method) · {{ $fight->result_method }} @endif
                    </div>
                </div>
                <span class="badge {{ $rc }}">{{ strtoupper(str_replace('_', ' ', $fight->result)) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($upcoming->count() === 0 && $history->count() === 0 && !$showForm)
    <div class="card text-center py-10">
        <div class="text-4xl mb-3">🥊</div>
        <div class="font-display text-xl font-bold mb-2">No Fights Yet</div>
        <p class="text-sm mb-4" style="color: var(--text-muted);">Add your upcoming fights or past record.</p>
        <button wire:click="$set('showForm', true)" class="btn-gold">Add First Fight</button>
    </div>
    @endif

</div>
