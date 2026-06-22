<div class="space-y-4 pb-4">

    {{-- Header --}}
    <div class="card card-glow">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                @if($profile?->avatar)
                    <img src="{{ Storage::url($profile->avatar) }}" class="w-20 h-20 rounded-2xl object-cover" style="border: 2px solid var(--blood);">
                @else
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">🥊</div>
                @endif
                <div>
                    <div class="font-display text-2xl font-bold">{{ auth()->user()->name }}</div>
                    @if($profile?->nickname)
                    <div class="text-sm" style="color: var(--gold);">"{{ $profile->nickname }}"</div>
                    @endif
                    @if($profile)
                    <div class="text-sm mt-1" style="color: var(--text-muted);">{{ $profile->weight_class ?? 'Boxer' }}</div>
                    @endif
                </div>
            </div>
            <button wire:click="$toggle('editing')" class="{{ $editing ? 'btn-ghost' : 'btn-primary' }} px-3 py-1.5 text-xs">
                {{ $editing ? 'Cancel' : 'Edit' }}
            </button>
        </div>

        @if($profile && !$editing)
        <div class="grid grid-cols-3 gap-3 mt-4 pt-4" style="border-top: 1px solid var(--dark-border);">
            <div class="text-center">
                <div class="font-display text-2xl font-bold" style="color: var(--gold);">{{ $profile->wins }}</div>
                <div class="text-xs" style="color: var(--text-muted);">Wins</div>
            </div>
            <div class="text-center">
                <div class="font-display text-2xl font-bold" style="color: var(--blood);">{{ $profile->losses }}</div>
                <div class="text-xs" style="color: var(--text-muted);">Losses</div>
            </div>
            <div class="text-center">
                <div class="font-display text-2xl font-bold">{{ $profile->draws }}</div>
                <div class="text-xs" style="color: var(--text-muted);">Draws</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Edit form --}}
    @if($editing)
    <form wire:submit="save" class="space-y-3">
        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Identity</div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Nickname</label>
                    <input type="text" wire:model="nickname" class="input-dark" placeholder="Iron Mike">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Weight Class</label>
                    <input type="text" wire:model="weight_class" class="input-dark" placeholder="Heavyweight">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Weight (kg)</label>
                    <input type="number" step="0.1" wire:model="current_weight" class="input-dark" placeholder="75.5">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Height (cm)</label>
                    <input type="number" wire:model="height_cm" class="input-dark" placeholder="180">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Reach (cm)</label>
                    <input type="number" wire:model="reach_cm" class="input-dark" placeholder="185">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Experience (years)</label>
                    <input type="number" wire:model="experience_years" class="input-dark" placeholder="5">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Stance</label>
                    <select wire:model="stance" class="input-dark">
                        <option value="orthodox">Orthodox</option>
                        <option value="southpaw">Southpaw</option>
                        <option value="switch">Switch</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Record</div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--gold);">Wins</label>
                    <input type="number" wire:model="wins" class="input-dark" placeholder="0">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--blood);">Losses</label>
                    <input type="number" wire:model="losses" class="input-dark" placeholder="0">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Draws</label>
                    <input type="number" wire:model="draws" class="input-dark" placeholder="0">
                </div>
            </div>
        </div>

        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Gym & Team</div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Gym</label>
                    <input type="text" wire:model="gym" class="input-dark" placeholder="Gym name">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Trainer</label>
                    <input type="text" wire:model="trainer" class="input-dark" placeholder="Trainer name">
                </div>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Bio</label>
                <textarea wire:model="bio" rows="3" class="input-dark" placeholder="Tell your story..."></textarea>
            </div>
        </div>

        <div class="card space-y-3">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Goals</div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Goal Weight</label>
                    <input type="number" step="0.1" wire:model="goal_weight" class="input-dark" placeholder="70.0">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Water Goal (L)</label>
                    <input type="number" step="0.1" wire:model="daily_water_goal_liters" class="input-dark">
                </div>
                <div>
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Cal Goal</label>
                    <input type="number" wire:model="daily_calorie_goal" class="input-dark">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Avatar</div>
            <input type="file" wire:model="avatar" class="input-dark text-xs" accept="image/*">
            @error('avatar') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary w-full py-3">Save Profile</button>
    </form>

    @else
    {{-- Profile view --}}
    @if($profile)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Stats</div>
        <div class="space-y-3">
            @foreach([
                ['Weight', ($profile->current_weight ? $profile->current_weight . ' kg → ' . ($profile->goal_weight ?? '?') . ' kg goal' : 'Not set'), '#3498db'],
                ['Height', ($profile->height_cm ? $profile->height_cm . ' cm' : 'Not set'), null],
                ['Reach', ($profile->reach_cm ? $profile->reach_cm . ' cm' : 'Not set'), null],
                ['Stance', ucfirst($profile->stance), null],
                ['Experience', $profile->experience_years . ' years', null],
                ['Gym', ($profile->gym ?? 'Not set'), null],
                ['Trainer', ($profile->trainer ?? 'Not set'), null],
            ] as [$label, $val, $color])
            <div class="flex items-center justify-between py-1.5" style="border-bottom: 1px solid var(--dark-border);">
                <span class="text-sm" style="color: var(--text-muted);">{{ $label }}</span>
                <span class="text-sm font-medium" @if($color) style="color: {{ $color }}" @endif>{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @if($profile->bio)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">Bio</div>
        <p class="text-sm leading-relaxed">{{ $profile->bio }}</p>
    </div>
    @endif

    @if($recentFights->count() > 0)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Fight History</div>
        <div class="space-y-2">
        @foreach($recentFights as $fight)
        <div class="flex items-center justify-between py-1.5" style="border-bottom: 1px solid var(--dark-border);">
            <div>
                <div class="text-sm font-medium">vs {{ $fight->opponent_name }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $fight->fight_date->format('M Y') }}</div>
            </div>
            @php $rc = ['win'=>'badge-green','loss'=>'badge-red','draw'=>'badge-gray','upcoming'=>'badge-gold'][$fight->result] ?? 'badge-gray' @endphp
            <span class="badge {{ $rc }}">{{ strtoupper($fight->result) }}</span>
        </div>
        @endforeach
        </div>
    </div>
    @endif

    @else
    <div class="card text-center py-8">
        <div class="text-4xl mb-3">🥊</div>
        <div class="font-display text-xl font-bold mb-2">Complete Your Profile</div>
        <p class="text-sm mb-4" style="color: var(--text-muted);">Add your stats so your AI coach knows you.</p>
        <button wire:click="$set('editing', true)" class="btn-primary">Setup Profile</button>
    </div>
    @endif
    @endif

</div>
