<div class="space-y-4 pb-4">
    @php
        $mealIcons = ['breakfast'=>'🌅','lunch'=>'☀️','dinner'=>'🌙','snack'=>'🍎','pre-workout'=>'⚡','post-workout'=>'💪'];
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">{{ __('Nutrition') }}</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-primary' }} text-xs px-3 py-1.5">
            {{ $showForm ? __('Cancel') : '+ '.__('Add Meal') }}
        </button>
    </div>

    {{-- Add meal form --}}
    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('What did you eat?') }}</label>
            <input type="text" wire:model="name" class="input-dark" placeholder="{{ __('e.g. Chicken & rice, pasta, protein shake') }}" autofocus>
            @error('name') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Meal') }}</label>
                <select wire:model="meal_type" class="input-dark">
                    <option value="breakfast">🌅 {{ __('Breakfast') }}</option>
                    <option value="lunch">☀️ {{ __('Lunch') }}</option>
                    <option value="dinner">🌙 {{ __('Dinner') }}</option>
                    <option value="snack">🍎 {{ __('Snack') }}</option>
                    <option value="pre-workout">⚡ {{ __('Pre-workout') }}</option>
                    <option value="post-workout">💪 {{ __('Post-workout') }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Time') }}</label>
                <input type="time" wire:model="eaten_time" class="input-dark">
            </div>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Details') }} <span style="opacity:0.6;">({{ __('optional') }})</span></label>
            <input type="text" wire:model="description" class="input-dark" placeholder="{{ __('Big portion, home-cooked, restaurant…') }}">
        </div>
        <button type="submit" class="btn-gold w-full py-2.5" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">{{ __('Log meal') }}</span>
            <span wire:loading wire:target="save">{{ __('CORNER is estimating…') }}</span>
        </button>
        <p class="text-xs text-center" style="color: var(--text-muted);">{{ __('CORNER estimates the calories - you confirm or adjust after.') }}</p>
    </form>
    @endif

    {{-- Today's total --}}
    @if($meals->count() > 0)
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs" style="color: var(--text-muted);">{{ __("Today's total") }}</div>
                <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ $confirmedCount }}/{{ $meals->count() }} {{ __('confirmed') }}{{ $confirmedCount < $meals->count() ? ' · '.__('confirm each below') : '' }}</div>
            </div>
            <div class="text-right leading-none">
                <span class="font-display text-3xl font-bold" style="color: {{ $confirmedCount === $meals->count() ? '#2ecc71' : 'var(--gold)' }};">~{{ number_format($total) }}</span>
                <span class="text-sm" style="color: var(--text-muted);">kcal</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Meals --}}
    <div class="space-y-2">
        @forelse($meals as $meal)
        <div wire:key="meal-{{ $meal->id }}" class="card flex items-start gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                {{ $mealIcons[$meal->meal_type] ?? '🍽️' }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm truncate">{{ $meal->name }}</div>
                <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __(ucwords(str_replace('-', ' ', $meal->meal_type))) }}{{ $meal->eaten_time ? ' · '.$meal->eaten_time : '' }}</div>

                <div class="mt-1.5">
                    @if($editId === $meal->id)
                    <div class="flex items-center gap-1.5">
                        <input type="number" min="0" max="6000" wire:model="editKcal" class="input-dark text-xs" style="width:78px; padding:0.3rem 0.5rem;">
                        <span class="text-xs" style="color: var(--text-muted);">kcal</span>
                        <button type="button" wire:click="saveFix" class="text-xs px-2 py-1 rounded-lg" style="color:#2ecc71; background:rgba(46,204,113,0.12);">{{ __('Save') }}</button>
                        <button type="button" wire:click="cancelFix" class="text-xs" style="color: var(--text-muted);">{{ __('Cancel') }}</button>
                    </div>
                    @elseif($meal->calories)
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-sm font-bold" style="color: {{ $meal->calories_source === 'confirmed' ? '#2ecc71' : 'var(--gold)' }};">~{{ number_format($meal->calories) }} kcal</span>
                        @if($meal->calories_source === 'confirmed')
                        <span class="text-xs" style="color:#2ecc71;">✓ {{ __('confirmed') }}</span>
                        <button type="button" wire:click="startFix({{ $meal->id }})" class="text-xs" style="color: var(--text-muted);">{{ __('edit') }}</button>
                        @else
                        <span class="text-xs" style="color: var(--text-muted);">{{ __('estimate - is it right?') }}</span>
                        @endif
                    </div>
                    @if($meal->calories_source !== 'confirmed')
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <button type="button" wire:click="confirmExact({{ $meal->id }})" class="text-xs px-2.5 py-1 rounded-lg" style="color:#2ecc71; background:rgba(46,204,113,0.12);">✓ {{ __('Exact') }}</button>
                        <button type="button" wire:click="startFix({{ $meal->id }})" class="text-xs px-2.5 py-1 rounded-lg" style="color:var(--gold); background:rgba(243,156,18,0.12);">✎ {{ __('More / less') }}</button>
                    </div>
                    @endif
                    @else
                    <span class="text-xs" style="color: var(--text-muted);">{{ __('estimating…') }}</span>
                    @endif
                </div>
            </div>

            <button type="button" wire:click="delete({{ $meal->id }})" wire:confirm="{{ __('Delete this meal?') }}" title="{{ __('Delete meal') }}" class="flex items-center justify-center rounded-lg flex-shrink-0" style="width:30px; height:30px; color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
        </div>
        @empty
        <div class="card text-center py-8">
            <div class="text-3xl mb-2">🍽️</div>
            <div class="text-sm" style="color: var(--text-muted);">{{ __('No meals logged today') }}</div>
            <button wire:click="$set('showForm', true)" class="btn-gold mt-3 text-sm">{{ __('Log first meal') }}</button>
        </div>
        @endforelse
    </div>

</div>
