<div class="pb-6">

    @php
        $totalFights = $profile ? ($profile->wins + $profile->losses + $profile->draws) : 0;
        $winRate     = $totalFights > 0 ? round(($profile->wins / $totalFights) * 100) : 0;
    @endphp

    {{-- ═══ FIGHTER HERO ═══ --}}
    <div class="card card-glow mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            @unless($editing)
            @if($profile?->avatar)
                <img src="{{ Storage::url($profile->avatar) }}"
                     class="w-24 h-24 rounded-2xl object-cover flex-shrink-0 mx-auto sm:mx-0"
                     style="border: 2px solid var(--blood);">
            @else
                <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-5xl flex-shrink-0 mx-auto sm:mx-0"
                     style="background: linear-gradient(145deg, var(--blood-dark), var(--blood));">🥊</div>
            @endif
            @endunless

            <div class="flex-1 min-w-0 text-center sm:text-left">
                <div class="font-display text-3xl font-bold leading-none">{{ auth()->user()->name }}</div>
                @if($profile?->nickname)
                <div class="text-base mt-1" style="color: var(--gold);">"{{ $profile->nickname }}"</div>
                @endif
                <div class="flex items-center justify-center sm:justify-start gap-2 mt-2 flex-wrap">
                    @if($profile?->weight_class)
                    <span class="badge badge-gray">{{ $profile->weight_class }}</span>
                    @endif
                    @if($profile)
                    <span class="badge badge-gray">{{ __(ucfirst($profile->stance)) }}</span>
                    @if($profile->experience_years > 0)
                    <span class="badge badge-gray">{{ $profile->experience_years }} {{ __('yr pro') }}</span>
                    @endif
                    @endif
                </div>
            </div>

            <button wire:click="$toggle('editing')"
                    class="{{ $editing ? 'btn-ghost' : 'btn-primary' }} px-4 py-2 text-sm flex-shrink-0 self-center">
                {{ $editing ? '✕ '.__('Cancel') : '✎ '.__('Edit') }}
            </button>
        </div>

        {{-- Record + win rate --}}
        @if($profile && !$editing)
        <div class="mt-4 pt-4" style="border-top: 1px solid var(--dark-border);">
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="font-display text-3xl font-bold" style="color: var(--gold);">{{ $profile->wins }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('Wins') }}</div>
                </div>
                <div class="text-center" style="border-left: 1px solid var(--dark-border); border-right: 1px solid var(--dark-border);">
                    <div class="font-display text-3xl font-bold" style="color: var(--blood);">{{ $profile->losses }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('Losses') }}</div>
                </div>
                <div class="text-center">
                    <div class="font-display text-3xl font-bold">{{ $profile->draws }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('Draws') }}</div>
                </div>
            </div>
            @if($totalFights > 0)
            <div class="mt-4">
                <div class="flex items-center justify-between mb-1.5 text-xs" style="color: var(--text-muted);">
                    <span>{{ __('Win rate') }}</span>
                    <span class="font-semibold" style="color: var(--gold);">{{ $winRate }}% · {{ $totalFights }} {{ __('pro fights') }}</span>
                </div>
                <div class="progress-track" style="height: 10px;">
                    <div class="progress-fill progress-gold" style="width: {{ $winRate }}%;"></div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ═══ EDIT FORM ═══ --}}
    @if($editing)
    @php $liveTotal = (int) $wins + (int) $losses + (int) $draws; @endphp
    <form wire:submit="save" class="space-y-4">

        {{-- Photo (with zoom & crop) --}}
        <div class="card flex items-center gap-4" x-data="avatarCropper()" @keydown.escape.window="closeModal()">
            @if($avatar)
                <img src="{{ $avatar->temporaryUrl() }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0" style="border: 2px solid var(--gold);">
            @elseif($profile?->avatar)
                <img src="{{ Storage::url($profile->avatar) }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0" style="border: 2px solid var(--blood);">
            @else
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl flex-shrink-0" style="background: linear-gradient(145deg, var(--blood-dark), var(--blood));">🥊</div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="section-label mb-1">{{ __('Profile Photo') }}</div>
                <p class="text-xs mb-2" style="color: var(--text-muted);">{{ __('Zoom and drag to frame it — any photo works') }}</p>
                <button type="button" @click="$refs.picker.click()" class="btn-ghost text-xs px-3 py-1.5 inline-block cursor-pointer">
                    <span x-show="!uploading">{{ __('Choose photo') }}</span>
                    <span x-show="uploading" style="color: var(--gold);">{{ __('Uploading…') }}</span>
                </button>
                <input type="file" x-ref="picker" class="hidden" accept="image/*" @change="openModal($event)">
                @error('avatar') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
            </div>

            {{-- Cropper modal (teleported to <body> so it overlays cleanly) --}}
            <template x-teleport="body">
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.82);">
                    <div class="card" style="max-width: 22rem; width: 100%;" @click.outside="closeModal()">
                        <div class="section-label mb-2">{{ __('Adjust your photo') }}</div>
                        <div style="height: 260px; background:#000; border-radius:14px; overflow:hidden;">
                            <img x-ref="image" style="max-width:100%; display:block;">
                        </div>
                        <div class="flex items-center gap-3 mt-3">
                            <span style="color: var(--text-muted);">🔍−</span>
                            <input type="range" min="0" max="1" step="0.01" value="0" x-ref="zoom" @input="setZoom($event.target.value)" class="flex-1" style="accent-color: var(--gold);">
                            <span style="color: var(--text-muted);">🔍+</span>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button type="button" @click="closeModal()" class="btn-ghost flex-1 py-2">{{ __('Cancel') }}</button>
                            <button type="button" @click="apply()" x-bind:disabled="uploading" class="btn-primary flex-1 py-2">
                                <span x-show="!uploading">{{ __('Apply') }}</span>
                                <span x-show="uploading">{{ __('Uploading…') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="grid gap-4 lg:grid-cols-2 items-start">

            {{-- Identity --}}
            <div class="card">
                <div class="section-label mb-3">🪪 {{ __('Identity') }}</div>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Nickname') }}</label>
                        <input type="text" wire:model="nickname" class="input-dark" placeholder="Iron Mike">
                        @error('nickname') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Weight class') }}</label>
                            <input type="text" wire:model="weight_class" class="input-dark" placeholder="Heavyweight">
                        </div>
                        <div>
                            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Date of birth') }}</label>
                            <input type="date" wire:model="date_of_birth" class="input-dark" max="{{ now()->toDateString() }}">
                            @error('date_of_birth') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Physique --}}
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label">📐 {{ __('Physique & Stance') }}</div>
                    <span class="text-xs" style="color: var(--text-muted);">{{ __('Weight is tracked via weigh-ins →') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Height (cm)') }}</label>
                        <input type="number" wire:model="height_cm" class="input-dark" placeholder="180" min="0" max="250">
                        @error('height_cm') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Reach (cm)') }}</label>
                        <input type="number" wire:model="reach_cm" class="input-dark" placeholder="185" min="0" max="300">
                        @error('reach_cm') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Years pro') }}</label>
                        <input type="number" wire:model="experience_years" class="input-dark" placeholder="5" min="0" max="50">
                    </div>
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Stance') }}</label>
                        <select wire:model="stance" class="input-dark">
                            <option value="orthodox">{{ __('Orthodox') }}</option>
                            <option value="southpaw">{{ __('Southpaw') }}</option>
                            <option value="switch">{{ __('Switch') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Record & Goal --}}
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label">🥊 {{ __('Record & Goal') }}</div>
                    <span class="text-xs" style="color: var(--text-muted);">{{ $liveTotal }} {{ __('total fights') }}</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--gold);">{{ __('Wins') }}</label>
                        <input type="number" wire:model.live="wins" class="input-dark" min="0">
                    </div>
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--blood);">{{ __('Losses') }}</label>
                        <input type="number" wire:model.live="losses" class="input-dark" min="0">
                    </div>
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Draws') }}</label>
                        <input type="number" wire:model.live="draws" class="input-dark" min="0">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Goal weight (kg)') }}</label>
                    <input type="number" step="0.1" wire:model="goal_weight" class="input-dark" placeholder="{{ __('Target fight weight') }}" min="0" max="200">
                    @error('goal_weight') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Gym & team --}}
            <div class="card">
                <div class="section-label mb-3">🏟️ {{ __('Gym & Team') }}</div>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Boxing gym') }}</label>
                        <input type="text" wire:model="gym" class="input-dark" placeholder="{{ __('Gym name') }}">
                    </div>
                    <div>
                        <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Trainer') }}</label>
                        <input type="text" wire:model="trainer" class="input-dark" placeholder="{{ __('Trainer name') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Bio --}}
        <div class="card">
            <div class="section-label mb-3">📝 {{ __('Bio') }}</div>
            <textarea wire:model="bio" rows="3" class="input-dark" placeholder="{{ __('Tell your story — CORNER uses this to coach you.') }}"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1 py-3">
                <span wire:loading.remove wire:target="save">{{ __('Save Profile') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
            </button>
            <button type="button" wire:click="$toggle('editing')" class="btn-ghost px-6 py-3">{{ __('Cancel') }}</button>
        </div>
    </form>

    @elseif($profile)
    {{-- ═══ TALE OF THE TAPE ═══ --}}
    <div class="card mb-4">
        <div class="section-label mb-3">{{ __('Tale of the Tape') }}</div>
        @php
            $vitals = [
                ['🎂', __('Age'),    $profile->date_of_birth ? $profile->date_of_birth->age . ' ' . __('yrs') : '—'],
                ['⚖️', __('Weight'), $currentWeight ? number_format($currentWeight, 1) . ' kg' : '—'],
                ['📏', __('Height'), $profile->height_cm ? $profile->height_cm . ' cm' : '—'],
                ['🤜', __('Reach'),  $profile->reach_cm ? $profile->reach_cm . ' cm' : '—'],
                ['🥊', __('Stance'), __(ucfirst($profile->stance))],
                ['⏳', __('Pro yrs'), $profile->experience_years ?: '—'],
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            @foreach($vitals as [$icon, $label, $val])
            <div class="stat-tile text-center">
                <div class="text-xl mb-1">{{ $icon }}</div>
                <div class="font-display text-lg font-bold leading-none">{{ $val }}</div>
                <div class="text-xs mt-1" style="color: var(--text-muted);">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        {{-- Goal weight — prominent, at the end --}}
        <div class="stat-tile stat-tile-gold mt-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">🎯</span>
                <span class="text-sm" style="color: var(--text-muted);">{{ __('Goal weight') }}</span>
            </div>
            <div class="font-display text-2xl font-bold" style="color: var(--gold);">{{ $profile->goal_weight ? $profile->goal_weight . ' kg' : '—' }}</div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 items-start">

        {{-- Weight goal --}}
        @if($currentWeight && $profile->goal_weight)
        @php $delta = round($currentWeight - $profile->goal_weight, 1); @endphp
        <div class="card">
            <div class="section-label mb-3">{{ __('Weight to Goal') }}</div>
            <div class="flex items-end justify-between">
                <div>
                    <div class="font-display text-3xl font-bold">{{ number_format($currentWeight, 1) }}<span class="text-base" style="color: var(--text-muted);"> kg</span></div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('current') }}{{ $weightAgo ? ' · ' . $weightAgo : '' }}</div>
                </div>
                <div class="text-center px-3">
                    @if($delta == 0)
                    <div class="font-display text-xl font-bold" style="color: #2ecc71;">{{ __('on weight') }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">✓ {{ __('at target') }}</div>
                    @else
                    <div class="font-display text-xl font-bold" style="color: {{ $delta > 0 ? 'var(--blood)' : 'var(--gold)' }};">{{ abs($delta) }} kg</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ $delta > 0 ? __('to cut') : __('to gain') }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="font-display text-3xl font-bold" style="color: var(--gold);">{{ $profile->goal_weight }}<span class="text-base" style="color: var(--text-muted);"> kg</span></div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('goal') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Gym & team --}}
        @if($profile->gym || $profile->trainer)
        <div class="card">
            <div class="section-label mb-3">{{ __('Gym & Team') }}</div>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-sm" style="color: var(--text-muted);">🏋️ {{ __('Boxing gym') }}</span>
                    <span class="text-sm font-medium">{{ $profile->gym ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm" style="color: var(--text-muted);">👤 {{ __('Trainer') }}</span>
                    <span class="text-sm font-medium">{{ $profile->trainer ?? '—' }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Bio --}}
        @if($profile->bio)
        <div class="card">
            <div class="section-label mb-2">{{ __('Bio') }}</div>
            <p class="text-sm leading-relaxed">{{ $profile->bio }}</p>
        </div>
        @endif

        {{-- Fight history --}}
        @if($recentFights->count() > 0)
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">{{ __('Recent Fights') }}</div>
                <a href="{{ route('fights') }}" class="text-xs" style="color: var(--gold); text-decoration: none;">{{ __('All') }} →</a>
            </div>
            <div class="space-y-0">
            @foreach($recentFights as $fight)
            <div class="flex items-center justify-between py-2.5" style="border-bottom: 1px solid var(--dark-border);">
                <div>
                    <div class="text-sm font-medium">vs {{ $fight->opponent_name }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ $fight->fight_date->translatedFormat('M Y') }}</div>
                </div>
                @php $rc = ['win'=>'badge-green','loss'=>'badge-red','draw'=>'badge-gray','upcoming'=>'badge-gold'][$fight->result] ?? 'badge-gray' @endphp
                <span class="badge {{ $rc }}">{{ mb_strtoupper(__($fight->result)) }}</span>
            </div>
            @endforeach
            </div>
        </div>
        @endif

    </div>

    @else
    {{-- ═══ EMPTY STATE ═══ --}}
    <div class="card text-center py-10">
        <div class="text-5xl mb-3">🥊</div>
        <div class="font-display text-2xl font-bold mb-2">{{ __('Complete Your Fighter Profile') }}</div>
        <p class="text-sm mb-5 max-w-sm mx-auto" style="color: var(--text-muted);">
            {{ __("Set up your record, vitals, and goal weight so CORNER's coaching is actually about you.") }}
        </p>
        <button wire:click="$set('editing', true)" class="btn-primary px-6 py-3">{{ __('Set Up Profile') }} →</button>
    </div>
    @endif

</div>
