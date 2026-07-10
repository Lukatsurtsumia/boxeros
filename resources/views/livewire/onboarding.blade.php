<div class="pb-6 space-y-4">
    <div class="card card-glow text-center">
        <div class="text-4xl mb-2">🥊</div>
        <div class="font-display text-2xl font-bold">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</div>
        <p class="text-sm mt-1" style="color: var(--text-muted);">{{ __("Let's set up your fighter profile - 30 seconds. You can change anything later.") }}</p>
    </div>

    <form wire:submit="save" class="card space-y-3">
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Nickname') }} <span style="opacity:0.6;">({{ __('optional') }})</span></label>
            <input type="text" wire:model="nickname" class="input-dark" placeholder="The Hammer">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Weight class') }}</label>
                <input type="text" wire:model="weight_class" class="input-dark" placeholder="Lightweight">
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Years boxing') }}</label>
                <input type="number" min="0" max="60" wire:model="experience_years" class="input-dark" placeholder="5">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Current weight (kg)') }}</label>
                <input type="number" step="0.1" min="30" max="200" wire:model="current_weight" class="input-dark" placeholder="74.0">
                @error('current_weight') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">{{ __('Goal weight (kg)') }}</label>
                <input type="number" step="0.1" min="30" max="200" wire:model="goal_weight" class="input-dark" placeholder="70.0">
                @error('goal_weight') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
            </div>
        </div>
        <button type="submit" class="btn-gold w-full py-2.5">{{ __('Start training') }} →</button>
        <button type="button" wire:click="skip" class="btn-ghost w-full py-2 text-sm">{{ __('Skip for now') }}</button>
    </form>

    <p class="text-xs text-center px-4" style="color: var(--text-muted);">
        {!! __('By continuing you agree to our :terms, :privacy and :disclaimer.', [
            'terms' => '<a href="'.route('terms').'" style="color: var(--gold);">'.__('Terms').'</a>',
            'privacy' => '<a href="'.route('privacy').'" style="color: var(--gold);">'.__('Privacy Policy').'</a>',
            'disclaimer' => '<a href="'.route('disclaimer').'" style="color: var(--gold);">'.__('Health Disclaimer').'</a>',
        ]) !!}
    </p>
</div>
