<div class="flex flex-col" style="height: calc(100vh - 130px);">

    {{-- Chat header --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">🤖</div>
            <div>
                <div class="font-display text-lg font-bold">CORNER</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ __('Your AI Boxing Coach') }}</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="startSession" class="btn-gold text-xs px-2.5 py-1">🎯 {{ __('New session') }}</button>
            <button wire:click="clearChat" wire:confirm="{{ __('Clear all chat history?') }}" class="btn-ghost text-xs px-2 py-1">{{ __('Clear') }}</button>
        </div>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto space-y-3 mb-3 pr-1" id="chat-messages"
         x-data="{}" @scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight">

        @if($messages->count() === 0)
        <div class="text-center pt-8 pb-4">
            <div class="text-5xl mb-4">🥊</div>
            <div class="font-display text-xl font-bold mb-2">{{ __('Talk to CORNER') }}</div>
            <p class="text-sm leading-relaxed px-4 mb-4" style="color: var(--text-muted);">
                {{ __("I know your full profile, today's stats, meals, and next fight. Ask me anything.") }}
            </p>
            <button type="button" wire:click="startSession" class="btn-primary w-full mb-4">🎯 {{ __('Start a coaching session') }}</button>
            <div class="text-xs mb-3" style="color: var(--text-muted);">— {{ __('or ask anything') }} —</div>
            <div class="space-y-2 text-left mx-2">
                @foreach([
                    __('How should I cut weight for my next fight?'),
                    __('My shoulder hurts after training. What should I do?'),
                    __('Give me a meal plan for fight week'),
                    __('How am I doing with my water intake?'),
                    __('I feel exhausted today. Is my training too intense?'),
                ] as $suggestion)
                <button type="button" wire:click="ask('{{ addslashes($suggestion) }}')" wire:loading.attr="disabled"
                        class="w-full text-left text-sm p-3 rounded-xl transition hover:border-white/20"
                        style="background: rgba(255,255,255,0.04); border: 1px solid var(--dark-border); color: var(--text-muted);">
                    {{ $suggestion }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        @foreach($messages as $msg)
        <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
            @if($msg->role === 'assistant')
            <div class="flex items-end gap-2 max-w-full">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm flex-shrink-0 mb-1" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">🤖</div>
                <div class="chat-bubble-ai">
                    <div class="chat-markdown">{!! \Illuminate\Support\Str::markdown($msg->content, ['html_input' => 'strip', 'allow_unsafe_links' => false, 'renderer' => ['soft_break' => '<br>']]) !!}</div>
                    <div class="text-xs mt-2" style="color: rgba(255,255,255,0.3);">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @else
            <div class="chat-bubble-user">
                {{ $msg->content }}
                <div class="text-xs mt-1" style="color: rgba(255,255,255,0.5);">{{ $msg->created_at->format('H:i') }}</div>
            </div>
            @endif
        </div>
        @endforeach

        @if($loading)
        <div class="flex justify-start">
            <div class="flex items-end gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm flex-shrink-0" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">🤖</div>
                <div class="chat-bubble-ai flex items-center gap-1">
                    <div class="flex gap-1">
                        <div class="w-2 h-2 rounded-full" style="background: var(--text-muted); animation: bounce 1s infinite 0s;"></div>
                        <div class="w-2 h-2 rounded-full" style="background: var(--text-muted); animation: bounce 1s infinite 0.2s;"></div>
                        <div class="w-2 h-2 rounded-full" style="background: var(--text-muted); animation: bounce 1s infinite 0.4s;"></div>
                    </div>
                    <span class="text-xs ml-2" style="color: var(--text-muted);">{{ __('Thinking...') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Input area --}}
    <div class="flex-shrink-0">
        <form wire:submit="sendMessage" class="flex gap-2">
            <input type="text" wire:model="message" id="corner-input"
                   class="input-dark flex-1"
                   placeholder="{{ __('Ask your coach anything...') }}"
                   {{ $loading ? 'disabled' : '' }}
                   autocomplete="off">
            <button type="submit" class="btn-primary px-4 flex-shrink-0" {{ $loading ? 'disabled' : '' }}>
                <span wire:loading.remove wire:target="sendMessage">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </span>
                <span wire:loading wire:target="sendMessage">⏳</span>
            </button>
        </form>
        @if(!config('services.anthropic.key'))
        <p class="text-xs text-center mt-2" style="color: var(--blood);">
            {!! __('Add <code>ANTHROPIC_API_KEY=your_key</code> to .env to enable AI coaching') !!}
        </p>
        @elseif($chatRemaining !== null)
        <p class="text-xs text-center mt-2" style="color: {{ $chatRemaining <= 3 ? 'var(--blood)' : 'var(--text-muted)' }};">
            @if($chatRemaining > 0)
                {{ $chatRemaining }} {{ $chatRemaining === 1 ? __('coaching message left today') : __('coaching messages left today') }}
            @else
                {{ __('Daily coaching limit reached — back tomorrow') }}
            @endif
        </p>
        @endif
    </div>

</div>

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

/* Rendered markdown inside CORNER's replies */
.chat-markdown > *:first-child { margin-top: 0; }
.chat-markdown > *:last-child { margin-bottom: 0; }
.chat-markdown p { margin: 0 0 0.6rem; }
.chat-markdown h1, .chat-markdown h2, .chat-markdown h3, .chat-markdown h4 {
    font-weight: 700; line-height: 1.25; margin: 0.85rem 0 0.45rem;
}
.chat-markdown h1 { font-size: 1.02rem; }
.chat-markdown h2 { font-size: 0.96rem; color: var(--gold); }
.chat-markdown h3, .chat-markdown h4 { font-size: 0.9rem; color: var(--gold); }
.chat-markdown ul, .chat-markdown ol { margin: 0.35rem 0 0.7rem; padding-left: 1.15rem; }
.chat-markdown li { margin: 0.2rem 0; }
.chat-markdown li::marker { color: var(--blood); }
.chat-markdown strong { font-weight: 700; color: #fff; }
.chat-markdown em { color: var(--text-muted); }
.chat-markdown a { color: var(--gold); text-decoration: underline; }
.chat-markdown hr { border: none; border-top: 1px solid var(--dark-border); margin: 0.75rem 0; }
.chat-markdown code { background: rgba(255,255,255,0.08); padding: 0.1rem 0.35rem; border-radius: 5px; font-size: 0.85em; }
.chat-markdown pre { background: rgba(0,0,0,0.35); padding: 0.7rem; border-radius: 10px; overflow-x: auto; margin: 0.5rem 0; }
.chat-markdown pre code { background: none; padding: 0; }
.chat-markdown blockquote { border-left: 3px solid var(--blood); margin: 0.5rem 0; padding-left: 0.75rem; color: var(--text-muted); }
.chat-markdown table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; font-size: 0.82rem; }
.chat-markdown th, .chat-markdown td { border: 1px solid var(--dark-border); padding: 0.35rem 0.5rem; text-align: left; }
.chat-markdown th { background: rgba(255,255,255,0.04); }
</style>

<script>
document.addEventListener('livewire:navigated', () => {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
});
window.addEventListener('scroll-to-bottom', () => {
    const el = document.getElementById('chat-messages');
    if (el) requestAnimationFrame(() => { el.scrollTop = el.scrollHeight; });
});
window.addEventListener('corner-input-clear', () => {
    const i = document.getElementById('corner-input');
    if (i) { i.value = ''; i.focus(); }
});
</script>
