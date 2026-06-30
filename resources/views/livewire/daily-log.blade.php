<div class="pb-6 space-y-4">

    @php
        $sodaSugar  = $soda_cans * 39;
        $sodaKcal   = $soda_cans * 140;
        $emptyKcal  = $sodaKcal + $alcoholKcal;
        $inCamp     = $fightDays !== null && $fightDays <= 42;
        $latestToday = $todayWeighIns->first();
        $moodMap    = ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'];
        $trainIcons = ['boxing'=>'🥊','sparring'=>'🥊','gym'=>'🏋️','running'=>'🏃','cycling'=>'🚴','swimming'=>'🏊','yoga'=>'🧘','other'=>'💪'];
        $ctxColor   = ['morning'=>'var(--gold)','afternoon'=>'#3498db','night'=>'#9b8cff','pre_workout'=>'#ff6b6b','post_workout'=>'#2ecc71','other'=>'var(--text-muted)'];
        $isToday    = $log_date === now()->toDateString();
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="font-display text-2xl font-bold">{{ __('Daily Log') }}</div>
            <div class="text-xs" style="color: var(--text-muted);">{{ now()->translatedFormat('l, F j') }}</div>
        </div>
        @if($fightDays !== null)
        <a href="{{ route('fights') }}" class="badge badge-gold" style="text-decoration: none;">🥊 {{ $fightDays }}{{ __('d to fight') }}</a>
        @endif
    </div>

    {{-- Today's plan → log straight against it --}}
    @if($isToday && $planToday && count($planToday['sessions']))
    <div class="card card-gold">
        <div class="section-label" style="color: var(--gold);">📋 {{ __('Your plan today') }}{{ $planToday['focus'] ? ' · '.$planToday['focus'] : '' }}</div>
        <div class="flex items-center gap-1.5 flex-wrap mt-2">
            @foreach($planToday['sessions'] as $ps)
            <span class="badge badge-gray">{{ $trainIcons[$ps['type']] ?? '💪' }} {{ __(ucfirst($ps['type'])) }} {{ $ps['minutes'] }}m</span>
            @endforeach
        </div>
        <div class="text-xs mt-3" style="color: var(--text-muted);">{!! __('That\'s your target for today — log what you <strong>actually</strong> do in Training sessions below.') !!}</div>
    </div>
    @elseif($isToday && $planToday && empty($planToday['sessions']))
    <div class="card" style="border-color: rgba(46,204,113,0.25);">
        <div class="text-sm" style="color: var(--text-muted);">😌 {{ __('Your plan says rest today — recover and refuel.') }}</div>
    </div>
    @endif

    {{-- ═══════════ RESULTS (today only) ═══════════ --}}
    @if($isToday)
    <div class="space-y-4">

        {{-- Weight --}}
        <div class="card">
            <div class="flex items-end justify-between mb-3">
                <div class="section-label">{{ __('Weight') }}</div>
                @if($latestToday)
                <div class="text-right leading-none">
                    <span class="font-display text-3xl font-bold">{{ number_format($latestToday->weight_kg, 1) }}</span>
                    <span class="text-sm" style="color: var(--text-muted);">kg</span>
                </div>
                @endif
            </div>
            @if($todayWeighIns->count() > 0)
            <div class="space-y-0">
                @foreach($todayWeighIns as $w)
                <div wire:key="weighin-{{ $w->id }}" class="flex items-center justify-between py-2" style="border-top: 1px solid var(--dark-border);">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs" style="color: var(--text-muted); min-width: 58px;">{{ $w->weighed_at->format('g:i A') }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                              style="background: rgba(255,255,255,0.05); color: {{ $ctxColor[$w->context] ?? 'var(--text-muted)' }};">{{ $w->context_label }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-display font-bold">{{ number_format($w->weight_kg, 1) }} <span class="text-xs" style="color: var(--text-muted);">kg</span></span>
                        <button type="button" wire:click="deleteWeighIn({{ $w->id }})" wire:confirm="{{ __('Delete this weigh-in?') }}"
                                class="text-xs" style="color: var(--text-muted);" title="{{ __('Delete weigh-in') }}">✕</button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-2 pt-2" style="border-top: 1px solid var(--dark-border);">
                @if($sweatLoss !== null)
                <span class="text-xs" style="color: var(--text-muted);">💦 {{ __('Sweat loss') }} <strong style="color: #3498db;">{{ $sweatLoss }} kg</strong></span>
                @else
                <span></span>
                @endif
                <button type="button" wire:click="clearTodayWeighIns" wire:confirm="{{ __('Delete ALL of today\'s weigh-ins?') }}"
                        class="text-xs" style="color: var(--text-muted);">{{ __('Clear all') }}</button>
            </div>
            @else
            <p class="text-sm" style="color: var(--text-muted);">{{ __('No weigh-in logged yet today.') }}</p>
            @endif
        </div>

        {{-- Today at a glance --}}
        <div class="card">
            <div class="section-label mb-3">{{ __('Today at a Glance') }}</div>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 text-center">
                <div>
                    <div class="font-display text-xl font-bold" style="color: #3498db;">{{ number_format($water_liters, 1) }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">L {{ __('water') }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: var(--gold);">{{ $training_minutes ? $training_minutes.'m' : '—' }}</div>
                    <div class="text-xs truncate" style="color: var(--text-muted);">{{ count($sessions) > 1 ? count($sessions).' '.__('sessions') : ($training_type ? __($training_type) : __('rest')) }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold">{{ $sleep_hours ?? '—' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ __('sleep h') }}</div>
                </div>
                <div>
                    <div class="text-2xl">{{ $moodMap[$mood] ?? '💪' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ __('mood') }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: var(--blood);">{{ $energy_level }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">{{ __('energy') }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: #2ecc71;">{{ $confirmedCalories ? number_format($confirmedCalories) : '—' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">kcal</div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Editing a past day --}}
    <div class="card" style="border-color: rgba(243,156,18,0.4);">
        <div class="flex items-center justify-between">
            <div>
                <div class="section-label" style="color: var(--gold);">{{ __('Editing a past day') }}</div>
                <div class="font-display text-lg font-bold">{{ \Carbon\Carbon::parse($log_date)->translatedFormat('l, M j') }}</div>
            </div>
            <button type="button" wire:click="closeForm" class="btn-ghost text-xs px-3 py-1.5">← {{ __('Back to today') }}</button>
        </div>
    </div>
    @endif

    {{-- Open form (today) --}}
    @if($isToday && !$showForm)
    <button type="button" wire:click="openForm" class="btn-primary w-full py-3">＋ {{ __('Log / edit today') }}</button>
    @endif

    {{-- ═══════════ FORM (Livewire-driven; auto-saves) ═══════════ --}}
    @if($showForm)
    <div id="logFormSection" class="space-y-4">

        <div class="flex items-center justify-between">
            <div class="section-label">{{ $isToday ? __('Logging today') : __('Editing') . ' ' . \Carbon\Carbon::parse($log_date)->translatedFormat('M j') }}</div>
            <div class="flex items-center gap-3">
                <span class="text-xs" wire:loading.remove style="color: #2ecc71;">✓ {{ __('Saved automatically') }}</span>
                <span class="text-xs" wire:loading style="color: var(--text-muted);">{{ __('Saving…') }}</span>
                <button type="button" wire:click="closeForm" class="btn-ghost text-xs px-3 py-1.5">{{ __('Done') }}</button>
            </div>
        </div>

        {{-- Logging inputs — masonry so cards pack tight (no empty gaps); 1 column on mobile --}}
        <style>
            .log-masonry { column-gap: 1rem; }
            @media (min-width: 1024px) { .log-masonry { column-count: 2; } }
            .log-masonry > * { break-inside: avoid; margin-bottom: 1rem; }
        </style>
        <div class="log-masonry">
            @if($isToday)
            @include('livewire.partials.weigh-in')
            @endif

            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label" style="color: #3498db;">💧 {{ __('Water') }}</div>
                    <div class="font-display text-2xl font-bold" style="color: #3498db;">{{ number_format($water_liters, 1) }} L</div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" wire:click="addWater(0.25)" class="water-btn">+250ml</button>
                    <button type="button" wire:click="addWater(0.5)"  class="water-btn">+500ml</button>
                    <button type="button" wire:click="addWater(0.75)" class="water-btn">+750ml</button>
                    <button type="button" wire:click="addWater(1)"    class="water-btn">+1L</button>
                </div>
                @if($water_liters > 0)
                <button type="button" wire:click="addWater(-0.25)" class="btn-ghost text-xs px-3 py-1.5 mt-2">↺ {{ __('Undo 250ml') }}</button>
                @endif
            </div>
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label">🥊 {{ __('Training sessions') }}</div>
                    @if($training_minutes)
                    <span class="text-xs" style="color: var(--text-muted);">{{ $training_minutes }}m · {{ count($sessions) }} {{ count($sessions) == 1 ? __('session') : __('sessions') }}</span>
                    @endif
                </div>
                <style>
                    .sess-no-spin::-webkit-inner-spin-button, .sess-no-spin::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
                    .sess-no-spin { -moz-appearance: textfield; }
                </style>
                @php
                    $sessIcons = ['boxing'=>'🥊','sparring'=>'🥊','gym'=>'🏋️','running'=>'🏃','cycling'=>'🚴','swimming'=>'🏊','yoga'=>'🧘','other'=>'💪'];
                @endphp
                @forelse($sessions as $i => $s)
                <div wire:key="session-{{ $i }}" class="mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid var(--dark-border); border-left: 3px solid var(--blood); border-radius: 12px; padding: 0.65rem;">
                    <div class="flex items-center gap-2 mb-2">
                        <span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:9px;background:rgba(192,57,43,0.14);font-size:1.05rem;flex-shrink:0;">{{ $sessIcons[$s['type'] ?? 'boxing'] ?? '💪' }}</span>
                        <select wire:model.live="sessions.{{ $i }}.type" class="input-dark" style="flex: 1; min-width: 0;">
                            @foreach($sessionTypes as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div style="position:relative; width:80px; flex-shrink:0;">
                            <input type="number" wire:model.blur="sessions.{{ $i }}.minutes" class="input-dark sess-no-spin" placeholder="0" min="0" max="600" style="width:100%; padding-right:32px; text-align:right;">
                            <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:0.7rem; color:var(--text-muted); pointer-events:none;">min</span>
                        </div>
                        <button type="button" wire:click="removeSession({{ $i }})" title="Remove session"
                                class="flex items-center justify-center flex-shrink-0"
                                style="width: 34px; height: 34px; border-radius: 9px; color: var(--text-muted); background: rgba(255,255,255,0.05);">✕</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <select wire:model.live="sessions.{{ $i }}.when" class="input-dark" style="flex: 1; min-width: 0;">
                            <option value="morning">🌅 {{ __('Morning') }}</option>
                            <option value="afternoon">☀️ {{ __('Afternoon') }}</option>
                            <option value="evening">🌙 {{ __('Evening') }}</option>
                        </select>
                        <input type="time" wire:model.blur="sessions.{{ $i }}.time" class="input-dark" style="flex: 1; min-width: 0;" title="{{ __('Exact time') }}">
                    </div>
                </div>
                @empty
                <div class="text-center mb-2" style="padding: 1.1rem; background: rgba(255,255,255,0.02); border: 1px dashed var(--dark-border); border-radius: 12px;">
                    <div style="font-size: 1.6rem; line-height: 1;">😌</div>
                    <div class="text-sm mt-1" style="color: var(--text-muted);">{{ __('Rest day — add a session if you trained') }}</div>
                </div>
                @endforelse
                <button type="button" wire:click="addSession" class="w-full text-sm font-semibold"
                        style="padding: 0.65rem; border: 1px dashed rgba(192,57,43,0.45); border-radius: 11px; color: #ff6b6b; background: transparent;">+ {{ __('Add session') }}</button>
                <div class="mt-4">
                    <label class="text-xs mb-1.5 block font-semibold" style="color: var(--text-muted);">😴 {{ __('Sleep last night') }}</label>
                    <div style="position:relative;">
                        <input type="number" step="0.5" wire:model.blur="sleep_hours" class="input-dark sess-no-spin" placeholder="8" min="0" max="24" style="width:100%; padding-right:42px;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.72rem; color:var(--text-muted); pointer-events:none;">hrs</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-label mb-3">🧠 {{ __('How you feel') }}</div>
                <div class="flex justify-around py-1">
                    @foreach(['great' => ['🔥',__('Great')], 'good' => ['💪',__('Good')], 'okay' => ['😐',__('Okay')], 'tired' => ['😴',__('Tired')], 'bad' => ['🤕',__('Rough')]] as $m => [$emoji, $label])
                    <button type="button" wire:click="$set('mood', '{{ $m }}')"
                            class="mood-btn flex flex-col items-center gap-1 {{ $mood === $m ? 'selected' : '' }}">
                        <span>{{ $emoji }}</span>
                        <span class="text-xs" style="color: var(--text-muted); font-size: 0.6rem;">{{ $label }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs" style="color: var(--text-muted);">{{ __('Energy') }}</span>
                        <span class="font-display text-lg font-bold" style="color: var(--blood);">{{ $energy_level }}/10</span>
                    </div>
                    <input type="range" min="1" max="10" wire:model.live.debounce.400ms="energy_level"
                           class="w-full" style="accent-color: var(--blood);">
                </div>
            </div>

        {{-- Sugar & alcohol --}}
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">🚫 {{ __('Sugar & alcohol') }}</div>
                @if($emptyKcal > 0)
                <span class="text-xs font-semibold" style="color: var(--blood);">≈ {{ number_format($emptyKcal) }} {{ __('empty kcal') }}</span>
                @endif
            </div>

            <label class="text-xs mb-1.5 block" style="color: var(--text-muted);">🥤 {{ __('Soda / sugary drinks') }}</label>
            <div class="flex gap-1.5 flex-wrap">
                @foreach([0=>__('None'),1=>'1',2=>'2',3=>'3',4=>'4',5=>'5+'] as $v => $lbl)
                <button type="button" wire:click="$set('soda_cans', {{ $v }})"
                        class="text-xs px-3 py-1.5 rounded-lg"
                        style="border: 1px solid {{ $soda_cans === $v ? 'var(--gold)' : 'var(--dark-border)' }};
                               background: {{ $soda_cans === $v ? 'rgba(243,156,18,0.15)' : 'transparent' }};
                               color: {{ $soda_cans === $v ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $lbl }}</button>
                @endforeach
            </div>
            @if($soda_cans > 0)
            <div class="text-xs mt-1.5" style="color: var(--text-muted);">≈ {{ $sodaSugar }}{{ __('g sugar') }} · {{ number_format($sodaKcal) }} kcal</div>
            @endif

            <label class="text-xs mb-2 mt-4 block font-semibold" style="color: var(--text-muted);">🍺 {{ __('Alcohol — tap what you drank') }}</label>
            <div>
                @foreach($alcoholTypes as $key => $d)
                <div class="flex items-center justify-between py-2" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span style="font-size: 1.2rem; flex-shrink: 0;">{{ $d[0] }}</span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold">{{ __($d[1]) }}</div>
                            <div class="text-xs" style="color: var(--text-muted);">{{ __($d[2]) }} · {{ $d[3] }} kcal</div>
                        </div>
                    </div>
                    <div class="flex items-center flex-shrink-0" style="background: rgba(255,255,255,0.04); border: 1px solid var(--dark-border); border-radius: 10px; overflow: hidden;">
                        <button type="button" wire:click="adjustDrink('{{ $key }}', -1)" style="width: 36px; height: 36px; color: var(--text-muted); font-size: 1.2rem; line-height: 1; background: transparent;">−</button>
                        <span class="font-display font-bold" style="width: 28px; text-align: center; font-size: 0.95rem; color: {{ ($alcohol_drinks[$key] ?? 0) > 0 ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $alcohol_drinks[$key] ?? 0 }}</span>
                        <button type="button" wire:click="adjustDrink('{{ $key }}', 1)" style="width: 36px; height: 36px; color: var(--blood); font-size: 1.2rem; line-height: 1; background: transparent;">+</button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($alcoholCount > 0)
            <div class="text-xs mt-2" style="color: var(--text-muted);">{{ $alcoholCount }} {{ $alcoholCount === 1 ? __('drink') : __('drinks') }} · ≈ {{ number_format($alcoholKcal) }} kcal · {{ __('dehydrating') }}</div>
            @endif

            <label class="text-xs mb-2 mt-4 block font-semibold" style="color: var(--text-muted);">☕ {{ __('Caffeine') }}</label>
            <div>
                @foreach($coffeeTypes as $key => $d)
                <div class="flex items-center justify-between py-2" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span style="font-size: 1.2rem; flex-shrink: 0;">{{ $d[0] }}</span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold">{{ __($d[1]) }}</div>
                            <div class="text-xs" style="color: var(--text-muted);">{{ __($d[2]) }} · {{ $d[3] }} kcal</div>
                        </div>
                    </div>
                    <div class="flex items-center flex-shrink-0" style="background: rgba(255,255,255,0.04); border: 1px solid var(--dark-border); border-radius: 10px; overflow: hidden;">
                        <button type="button" wire:click="adjustDrink('{{ $key }}', -1)" style="width: 36px; height: 36px; color: var(--text-muted); font-size: 1.2rem; line-height: 1; background: transparent;">−</button>
                        <span class="font-display font-bold" style="width: 28px; text-align: center; font-size: 0.95rem; color: {{ ($alcohol_drinks[$key] ?? 0) > 0 ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $alcohol_drinks[$key] ?? 0 }}</span>
                        <button type="button" wire:click="adjustDrink('{{ $key }}', 1)" style="width: 36px; height: 36px; color: var(--blood); font-size: 1.2rem; line-height: 1; background: transparent;">+</button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($coffeeCups > 0)
            <div class="text-xs mt-2" style="color: var(--text-muted);">{{ $coffeeCups }} {{ $coffeeCups === 1 ? __('cup') : __('cups') }} {{ __('today') }}</div>
            @endif

            @if(($soda_cans > 0 || $alcoholCount > 0) && $inCamp)
            <div class="mt-3 pt-3 text-xs" style="border-top: 1px solid var(--dark-border); color: #ff6b6b;">
                ⚠ {{ $fightDays }} {{ __('days to your fight — sugar & alcohol stall your cut and slow recovery.') }}
            </div>
            @endif
        </div>

        {{-- Notes --}}
        <div class="card">
            <div class="section-label mb-2">📝 {{ __('Notes') }}</div>
            <textarea wire:model.blur="notes" rows="2" class="input-dark" placeholder="{{ __('How did training go? Anything to remember?') }}"></textarea>
        </div>
        </div>

        <button type="button" wire:click="closeForm" class="btn-primary w-full py-3">{{ __('Done') }}</button>
        <p class="text-xs text-center" style="color: var(--text-muted);">{{ __('Everything you tap or type here saves automatically.') }}</p>
    </div>
    @endif

    {{-- History --}}
    @if($logs->count() > 0)
    <div class="card">
        <div class="flex items-baseline justify-between mb-3">
            <div class="section-label">{{ __('Recent days') }}</div>
            <span class="text-xs" style="color: var(--text-muted);">{{ __('tap Edit to fix any day') }}</span>
        </div>
        <div class="space-y-2">
            @foreach($logs as $log)
            @php
                $dKey      = $log->log_date->format('Y-m-d');
                $dayName   = $log->log_date->isToday() ? __('Today') : ($log->log_date->isYesterday() ? __('Yesterday') : $log->log_date->translatedFormat('M j'));
                $sessCount = is_array($log->sessions) ? count($log->sessions) : 0;
            @endphp
            <div wire:key="log-{{ $dKey }}" class="rounded-xl p-3 transition hover:bg-white/5"
                 style="background: rgba(255,255,255,0.02); border-left: 3px solid {{ $log->log_date->isToday() ? 'var(--gold)' : 'transparent' }};">

                {{-- Date + mood + actions --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-lg flex-shrink-0">{{ $moodMap[$log->mood ?? 'good'] ?? '💪' }}</span>
                        <span class="text-sm font-bold flex-shrink-0" style="{{ $log->log_date->isToday() ? 'color: var(--gold);' : '' }}">{{ $dayName }}</span>
                        <span class="text-xs truncate" style="color: var(--text-muted);">{{ $log->log_date->translatedFormat('D, M j') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" wire:click="editLog('{{ $dKey }}')"
                                class="text-xs px-3 py-1 rounded-lg"
                                style="border: 1px solid rgba(243,156,18,0.3); color: var(--gold); background: rgba(243,156,18,0.08);">{{ __('Edit') }}</button>
                        <button type="button" wire:click="deleteLog('{{ $dKey }}')" wire:confirm="{{ __('Delete this day\'s log?') }}" title="{{ __('Delete day') }}"
                                class="flex items-center justify-center rounded-lg"
                                style="width: 28px; height: 28px; color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
                    </div>
                </div>

                {{-- Stats — fixed grid so layout never shifts --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <div class="font-display text-base font-bold leading-none">{{ isset($weightByDate[$dKey]) ? number_format($weightByDate[$dKey], 1) : '—' }}<span class="text-xs font-normal" style="color: var(--text-muted);"> kg</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">⚖️ {{ __('Weight') }}</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none" style="color: #3498db;">{{ number_format($log->water_liters, 1) }}<span class="text-xs font-normal" style="color: var(--text-muted);"> L</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">💧 {{ __('Water') }}</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none" style="color: {{ $log->training_minutes ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $log->training_minutes ? $log->training_minutes.'m' : __('Rest') }}</div>
                        <div class="text-xs mt-1 truncate" style="color: var(--text-muted);">{{ $log->training_minutes ? ($trainIcons[$log->training_type] ?? '🏋️').' '.($sessCount > 1 ? $sessCount.' '.__('sessions') : __($log->training_type)) : '💤 '.__('rest day') }}</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none">{{ $log->sleep_hours ?? '—' }}<span class="text-xs font-normal" style="color: var(--text-muted);"> h</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">😴 {{ __('Sleep') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
