<div class="space-y-4 pb-4">

    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">{{ __('Fights') }}</div>
        @if($showForm)
        <button type="button" wire:click="cancelForm" class="btn-ghost text-xs px-3 py-1.5">{{ __('Cancel') }}</button>
        @else
        <button type="button" wire:click="addNew" class="btn-gold text-xs px-3 py-1.5">+ {{ __('Add Fight') }}</button>
        @endif
    </div>

    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div class="section-label">{{ $editingId ? __('Edit fight') : __('Add fight') }}</div>
        <div class="grid grid-cols-2 gap-2">
            <div class="col-span-2">
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Opponent') }}</label>
                <input type="text" wire:model="opponent_name" class="input-dark" placeholder="John Doe" required>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Fight Date') }}</label>
                <input type="datetime-local" wire:model="fight_date" class="input-dark">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Rounds') }}</label>
                <input type="number" wire:model="rounds" class="input-dark" min="1" max="15">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Event') }}</label>
                <input type="text" wire:model="event_name" class="input-dark" placeholder="{{ __('Event name') }}">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Location') }}</label>
                <input type="text" wire:model="location" class="input-dark" placeholder="{{ __('City, Country') }}">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Weight Class') }}</label>
                <input type="text" wire:model="weight_class" class="input-dark" placeholder="{{ __('Lightweight') }}">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Result') }}</label>
                <select wire:model.live="result" class="input-dark">
                    <option value="upcoming">{{ __('Upcoming') }}</option>
                    <option value="win">{{ __('Win') }}</option>
                    <option value="loss">{{ __('Loss') }}</option>
                    <option value="draw">{{ __('Draw') }}</option>
                    <option value="no_contest">{{ __('No Contest') }}</option>
                </select>
            </div>
            @if($result !== 'upcoming')
            <div class="col-span-2">
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Method (KO, TKO, UD...)') }}</label>
                <input type="text" wire:model="result_method" class="input-dark" placeholder="KO R3">
            </div>
            @endif
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Notes') }}</label>
            <textarea wire:model="notes" rows="2" class="input-dark" placeholder="{{ __('Fight notes...') }}"></textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-gold flex-1 py-2.5">{{ $editingId ? __('Update Fight') : __('Save Fight') }}</button>
            <button type="button" wire:click="cancelForm" class="btn-ghost px-5 py-2.5">{{ __('Cancel') }}</button>
        </div>
    </form>
    @endif

    {{-- Upcoming fights --}}
    @if($upcoming->count() > 0)
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">{{ __('Upcoming') }}</div>
        @foreach($upcoming as $fight)
        @php
            $secs = (int) max(0, now()->diffInSeconds($fight->fight_date, false));
            $cd   = intdiv($secs, 86400);
            $ch   = intdiv($secs % 86400, 3600);
        @endphp
        <div wire:key="fight-{{ $fight->id }}" class="card card-gold mb-3">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-semibold mb-1" style="color: var(--gold);">{{ $fight->fight_date->translatedFormat('D, M d · H:i') }}</div>
                    <div class="font-display text-2xl font-bold">vs {{ $fight->opponent_name }}</div>
                    @if($fight->event_name)
                    <div class="text-sm mt-0.5" style="color: var(--text-muted);">{{ $fight->event_name }}</div>
                    @endif
                    @if($fight->location)
                    <div class="text-xs mt-1" style="color: var(--text-muted);">📍 {{ $fight->location }}</div>
                    @endif
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    @if($cd > 0 || $ch > 0)
                    <div class="font-display text-3xl font-bold leading-none" style="color: var(--gold);">{{ $cd }}<span class="text-lg">d</span> {{ $ch }}<span class="text-lg">h</span></div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('to go') }}</div>
                    @else
                    <div class="font-display text-2xl font-bold leading-none" style="color: var(--gold);">{{ __('Fight time!') }}</div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
                @if($fight->weight_class) <span class="badge badge-gray">{{ $fight->weight_class }}</span> @endif
                <span class="badge badge-gray">{{ $fight->rounds }}R</span>
                <button type="button" wire:click="edit({{ $fight->id }})" class="ml-auto text-xs px-2.5 py-1 rounded-lg" style="border: 1px solid rgba(243,156,18,0.3); color: var(--gold); background: rgba(243,156,18,0.08);">{{ __('Edit / record result') }}</button>
                <button type="button" wire:click="delete({{ $fight->id }})" wire:confirm="{{ __('Remove this fight from your record?') }}" class="text-xs" style="color: var(--text-muted);">{{ __('Remove') }}</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Fight history --}}
    @if($history->count() > 0)
    @php
        $wins   = $history->where('result', 'win')->count();
        $losses = $history->where('result', 'loss')->count();
        $draws  = $history->where('result', 'draw')->count();
        $resultStyle = [
            'win'        => ['W',  '#2ecc71', 'rgba(46,204,113,0.15)'],
            'loss'       => ['L',  '#ff6b6b', 'rgba(192,57,43,0.18)'],
            'draw'       => ['D',  'var(--text-muted)', 'rgba(255,255,255,0.06)'],
            'no_contest' => ['NC', 'var(--text-muted)', 'rgba(255,255,255,0.06)'],
        ];
    @endphp
    <div>
        <div class="flex items-center justify-between mb-2">
            <div class="section-label">{{ __('History') }}</div>
            <div class="text-xs font-semibold">
                <span style="color: #2ecc71;">{{ $wins }}W</span>
                <span style="color: rgba(255,255,255,0.2);">·</span>
                <span style="color: #ff6b6b;">{{ $losses }}L</span>
                <span style="color: rgba(255,255,255,0.2);">·</span>
                <span style="color: var(--text-muted);">{{ $draws }}D</span>
            </div>
        </div>
        <div class="space-y-2">
            @foreach($history as $fight)
            @php $rs = $resultStyle[$fight->result] ?? ['?', 'var(--text-muted)', 'rgba(255,255,255,0.06)']; @endphp
            <div wire:key="fight-{{ $fight->id }}" class="flex items-center gap-3 p-3 rounded-xl transition hover:bg-white/5" style="background: rgba(255,255,255,0.02);">
                <div class="flex items-center justify-center rounded-full flex-shrink-0 font-display font-bold"
                     style="width: 42px; height: 42px; background: {{ $rs[2] }}; color: {{ $rs[1] }};">{{ $rs[0] }}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">vs {{ $fight->opponent_name }}</div>
                    <div class="text-xs truncate" style="color: var(--text-muted);">
                        {{ $fight->fight_date->translatedFormat('M j, Y') }}
                        @if($fight->rounds) · {{ $fight->rounds }}R @endif
                        @if($fight->result_method) · {{ $fight->result_method }} @endif
                    </div>
                </div>
                <button type="button" wire:click="edit({{ $fight->id }})"
                        class="text-xs px-3 py-1.5 rounded-lg flex-shrink-0"
                        style="border: 1px solid rgba(243,156,18,0.3); color: var(--gold); background: rgba(243,156,18,0.08);">{{ __('Edit') }}</button>
                <button type="button" wire:click="delete({{ $fight->id }})" wire:confirm="{{ __('Delete this fight?') }}" title="{{ __('Delete fight') }}"
                        class="flex items-center justify-center rounded-lg flex-shrink-0"
                        style="width: 30px; height: 30px; color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($upcoming->count() === 0 && $history->count() === 0 && !$showForm)
    <div class="card text-center py-10">
        <div class="text-4xl mb-3">🥊</div>
        <div class="font-display text-xl font-bold mb-2">{{ __('No Fights Yet') }}</div>
        <p class="text-sm mb-4" style="color: var(--text-muted);">{{ __('Add your upcoming fights or past record.') }}</p>
        <button type="button" wire:click="addNew" class="btn-gold">{{ __('Add First Fight') }}</button>
    </div>
    @endif

</div>
