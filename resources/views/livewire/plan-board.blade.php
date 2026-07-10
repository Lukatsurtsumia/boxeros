<div class="space-y-4 pb-4" id="plan-top">
    @php
        $icons = ['boxing'=>'🥊','sparring'=>'🥊','gym'=>'🏋️','running'=>'🏃','other'=>'💪'];
        $dayLabels = \App\Models\Plan::DAY_LABELS;
        $todayKey = \App\Models\Plan::DAYS[now()->dayOfWeekIso - 1];
        $planTypes = \App\Models\Plan::PLAN_TYPES;
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <div class="font-display text-2xl font-bold">{{ __('My Plan') }}</div>
            <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('Built by CORNER · edit anything · the dashboard tracks it') }}</div>
        </div>
        @unless($editing)
        <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <button type="button" wire:click="generate"
                    wire:confirm="{{ __("Build a fresh plan with CORNER? This uses one of today's plan builds.") }}"
                    wire:loading.attr="disabled" wire:target="generate,runGenerate"
                    {{ ($plansLeft !== null && $plansLeft <= 0) ? 'disabled' : '' }}
                    class="btn-gold text-xs px-3 py-1.5" style="{{ ($plansLeft !== null && $plansLeft <= 0) ? 'opacity:0.45;' : '' }}">
                <span wire:loading.remove wire:target="generate,runGenerate">{{ $plan ? '↻ '.__('Regenerate') : '✨ '.__('Generate') }}</span>
                <span wire:loading wire:target="generate,runGenerate">{{ __('Building…') }}</span>
            </button>
            @if($plansLeft !== null)
            <span class="text-xs" style="color: {{ $plansLeft <= 1 ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $plansLeft }} {{ $plansLeft === 1 ? __('build left today') : __('builds left today') }}</span>
            @endif
        </div>
        @endunless
    </div>

    @if($planLimitHit)
    <div class="card" style="border-color: rgba(243,156,18,0.4);">
        <div class="text-sm font-semibold" style="color: var(--gold);">{{ __("You've used today's plan builds") }}</div>
        <div class="text-xs mt-1 leading-relaxed" style="color: var(--text-muted);">{!! __('CORNER builds :count fresh plans a day to keep costs in check. Adjust your current plan by hand instead - tap <strong>Edit</strong> (free &amp; instant) - or regenerate tomorrow.', ['count' => \App\Support\Corner::DAILY_LIMITS['plan']]) !!}</div>
    </div>
    @endif

    <div wire:loading.flex wire:target="generate,runGenerate" class="card items-center justify-center py-12" style="display:none;">
        <div class="text-center">
            <div class="text-3xl mb-2">🤖</div>
            <div class="text-sm font-semibold">{{ __('CORNER is building your week…') }}</div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('Periodising training, nutrition and weight') }}</div>
        </div>
    </div>

    <div wire:loading.remove wire:target="generate,runGenerate" class="space-y-4">

    @if(!$plan)
        <div class="card text-center py-10">
            <div class="text-4xl mb-3">📋</div>
            <div class="font-display text-xl font-bold mb-2">{{ __('No plan yet') }}</div>
            <p class="text-sm mb-4 px-4" style="color: var(--text-muted);">
                {{ __('Let CORNER build a personalised championship week - periodised training, day-by-day nutrition and a moving weight target. Then edit anything and follow it.') }}
            </p>
            <button type="button" wire:click="generate" class="btn-gold">✨ {{ __('Generate my plan') }}</button>
        </div>
    @elseif($editing)
        {{-- ════ EDIT MODE - full control ════ --}}
        <div class="card card-gold">
            <div class="font-display text-lg font-bold mb-1">{{ __('Editing plan') }}</div>
            <div class="text-xs" style="color: var(--text-muted);">{{ __("Change sessions, minutes, calories, sleep and weight for any day. Set minutes to 0 to drop a session; clear a day's sessions for a rest day.") }}</div>
        </div>

        @foreach(\App\Models\Plan::DAYS as $day)
        <div class="card" style="padding: 0.85rem 1rem;">
            <div class="flex items-center gap-2 mb-2">
                <span class="font-display font-bold w-9">{{ __($dayLabels[$day]) }}</span>
                <input type="text" wire:model="draft.{{ $day }}.focus" class="input-dark text-xs" placeholder="{{ __('Focus (e.g. Sparring + core)') }}" style="flex:1; padding:0.4rem 0.6rem;">
            </div>

            <div class="space-y-1.5 mb-2">
                @foreach($draft[$day]['sessions'] ?? [] as $i => $s)
                <div wire:key="sess-{{ $day }}-{{ $i }}" class="mb-2">
                    <div class="flex items-center gap-1.5">
                        <select wire:model="draft.{{ $day }}.sessions.{{ $i }}.type" class="input-dark text-xs" style="flex:1; padding:0.4rem 0.5rem;">
                            @foreach($planTypes as $t)<option value="{{ $t }}">{{ __(ucfirst($t)) }}</option>@endforeach
                        </select>
                        <input type="number" min="0" max="600" wire:model="draft.{{ $day }}.sessions.{{ $i }}.minutes" class="input-dark text-xs" style="width:64px; padding:0.4rem 0.5rem;" placeholder="min">
                        <button type="button" wire:click="removeSession('{{ $day }}', {{ $i }})" class="text-xs px-2" style="color: var(--blood);">✕</button>
                    </div>
                    <input type="text" wire:model="draft.{{ $day }}.sessions.{{ $i }}.detail" class="input-dark text-xs w-full mt-1" style="padding:0.4rem 0.5rem;" placeholder="{{ __('What to work on - rounds, drills, intensity') }}">
                </div>
                @endforeach
                <button type="button" wire:click="addSession('{{ $day }}')" class="text-xs" style="color: var(--gold);">+ {{ __('add session') }}</button>
            </div>

            <div class="grid grid-cols-3 gap-1.5">
                <div>
                    <label class="text-xs" style="color: var(--text-muted);">🍽️ kcal</label>
                    <input type="number" min="0" max="8000" wire:model="draft.{{ $day }}.calories" class="input-dark text-xs" style="padding:0.4rem 0.5rem;">
                </div>
                <div>
                    <label class="text-xs" style="color: var(--text-muted);">😴 {{ __('sleep h') }}</label>
                    <input type="number" step="0.5" min="0" max="14" wire:model="draft.{{ $day }}.sleep" class="input-dark text-xs" style="padding:0.4rem 0.5rem;">
                </div>
                <div>
                    <label class="text-xs" style="color: var(--text-muted);">⚖ kg</label>
                    <input type="number" step="0.1" min="30" max="200" wire:model="draft.{{ $day }}.weight" class="input-dark text-xs" style="padding:0.4rem 0.5rem;">
                </div>
            </div>
        </div>
        @endforeach

        <div class="flex gap-2 sticky bottom-2">
            <button type="button" wire:click="saveEdit" class="btn-gold flex-1 py-3">✓ {{ __('Save plan') }}</button>
            <button type="button" wire:click="cancelEdit" class="btn-ghost px-5 py-3">{{ __('Cancel') }}</button>
        </div>
    @else
        @php $days = $plan->days(); @endphp

        {{-- Plan header --}}
        <div class="card {{ $plan->is_active ? 'card-gold' : '' }}">
            <div class="flex items-start justify-between gap-2">
                <div class="font-display text-xl font-bold leading-tight">{{ $plan->title }}</div>
                @if($plan->is_active)<span class="badge badge-gold flex-shrink-0">● {{ __('Following') }}</span>@else<span class="badge badge-gray flex-shrink-0">{{ __('Draft') }}</span>@endif
            </div>
            @if($plan->notes)<div class="text-xs mt-2 leading-relaxed" style="color: var(--text-muted);">{{ $plan->notes }}</div>@endif
            <div class="flex items-center gap-3 mt-3 text-xs" style="color: var(--text-muted);">
                <span>🥊 {{ 7 - $plan->restDayCount() }} {{ __('training') }}</span>
                <span>😌 {{ $plan->restDayCount() }} {{ __('rest') }}</span>
                @if($plan->target_weight)<span>⚖ {{ __('ends ~:kg kg', ['kg' => rtrim(rtrim(number_format($plan->target_weight,1),'0'),'.')]) }}</span>@endif
            </div>

            <div class="flex gap-2 mt-3">
                @if($plan->is_active)
                <a href="{{ route('daily.log') }}" wire:navigate class="btn-gold flex-1 py-2.5 text-center" style="text-decoration:none;">✍️ {{ __('Log today') }}</a>
                <button type="button" wire:click="editPlan" class="btn-ghost px-4 py-2.5">{{ __('Edit') }}</button>
                <button type="button" wire:click="deactivate({{ $plan->id }})" class="btn-ghost px-4 py-2.5">{{ __('Stop') }}</button>
                @else
                <button type="button" wire:click="activate({{ $plan->id }})" class="btn-gold flex-1 py-2.5">✓ {{ __('Follow this plan') }}</button>
                <button type="button" wire:click="editPlan" class="btn-ghost px-4 py-2.5">{{ __('Edit') }}</button>
                <button type="button" wire:click="delete({{ $plan->id }})" wire:confirm="{{ __('Delete this plan?') }}" class="btn-ghost px-4 py-2.5">🗑</button>
                @endif
            </div>
        </div>

        {{-- The week, day by day --}}
        <div class="space-y-2">
            @foreach(\App\Models\Plan::DAYS as $day)
            @php $d = $days[$day]; $isToday = $day === $todayKey; $isRest = empty($d['sessions']); @endphp
            <div class="card {{ $isToday ? 'card-glow' : '' }}" style="padding: 0.85rem 1rem;{{ $isRest ? ' opacity:0.75;' : '' }}">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-display font-bold" style="color: {{ $isToday ? 'var(--gold)' : 'inherit' }};">{{ __($dayLabels[$day]) }}</span>
                        @if($isToday)<span class="badge badge-gold">{{ __('Today') }}</span>@endif
                    </div>
                    @if($d['focus'])<span class="text-xs" style="color: var(--text-muted);">{{ $d['focus'] }}</span>@endif
                </div>

                @if($isRest)
                <div class="text-sm mb-2" style="color: var(--text-muted);">😌 {{ __('Rest & recover') }}</div>
                @else
                <div class="space-y-2 mb-2">
                    @foreach($d['sessions'] as $s)
                    <div class="rounded-xl p-2.5" style="background: rgba(255,255,255,0.025); border: 1px solid var(--dark-border); border-left: 3px solid var(--blood);">
                        <div class="flex items-center gap-2">
                            <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(192,57,43,0.14);font-size:0.95rem;flex-shrink:0;">{{ $icons[$s['type']] ?? '💪' }}</span>
                            <span class="text-sm font-semibold flex-1">{{ __(ucfirst($s['type'])) }}</span>
                            <span class="text-xs font-display font-bold" style="color: var(--gold);">{{ $s['minutes'] }} min</span>
                        </div>
                        @if(!empty($s['detail']))
                        <div class="text-xs leading-relaxed mt-1.5" style="color: var(--text-muted); padding-left: 36px;">{{ $s['detail'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="flex items-center gap-3 flex-wrap text-xs pt-2" style="border-top: 1px solid var(--dark-border); color: var(--text-muted);">
                    @if($d['calories'])<span>🍽️ {{ number_format($d['calories']) }} kcal{{ $d['protein'] ? ' · '.$d['protein'].__('g P') : '' }}</span>@endif
                    @if($d['weight'])<span>⚖ {{ rtrim(rtrim(number_format($d['weight'],1),'0'),'.') }} kg</span>@endif
                    @if($d['sleep'])<span>😴 {{ rtrim(rtrim(number_format($d['sleep'],1),'0'),'.') }}h</span>@endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Other saved plans --}}
        @if($plans->count() > 1)
        <div>
            <div class="section-label mb-2">{{ __('Saved plans') }}</div>
            <div class="space-y-1.5">
                @foreach($plans as $p)
                @if($p->id !== $plan->id)
                <div wire:key="plan-{{ $p->id }}" class="flex items-center gap-2 p-2.5 rounded-xl" style="background: rgba(255,255,255,0.02);">
                    <button type="button" wire:click="show({{ $p->id }})" class="flex-1 text-left text-sm truncate" style="background:none;">{{ $p->title }}</button>
                    <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="{{ __('Delete this plan?') }}" class="text-xs flex-shrink-0" style="color: var(--blood);">✕</button>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    @endif

    </div>

    <script>
        window.addEventListener('scrollTop', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    </script>
</div>
