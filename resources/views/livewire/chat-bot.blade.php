<div class="flex flex-col" style="height: calc(100vh - 130px);">

    {{-- Chat header --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">🤖</div>
            <div>
                <div class="font-display text-lg font-bold">CORNER</div>
                <div class="text-xs" style="color: var(--text-muted);">Your AI Boxing Coach</div>
            </div>
        </div>
        <button wire:click="clearChat" wire:confirm="Clear all chat history?" class="btn-ghost text-xs px-2 py-1">Clear</button>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto space-y-3 mb-3 pr-1" id="chat-messages"
         x-data="{}" @scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight">

        @if($messages->count() === 0)
        <div class="text-center pt-8 pb-4">
            <div class="text-5xl mb-4">🥊</div>
            <div class="font-display text-xl font-bold mb-2">Talk to CORNER</div>
            <p class="text-sm leading-relaxed px-4 mb-4" style="color: var(--text-muted);">
                I know your full profile, today's stats, injuries, and next fight. Ask me anything.
            </p>
            <div class="space-y-2 text-left mx-2">
                @foreach([
                    'How should I cut weight for my next fight?',
                    'My shoulder hurts after training. What should I do?',
                    'Give me a meal plan for fight week',
                    'How am I doing with my water intake?',
                    'I feel exhausted today. Is my training too intense?',
                ] as $suggestion)
                <button wire:click="$set('message', '{{ addslashes($suggestion) }}')"
                        class="w-full text-left text-sm p-3 rounded-xl"
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
                    {!! nl2br(e($msg->content)) !!}
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
                    <span class="text-xs ml-2" style="color: var(--text-muted);">Thinking...</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Input area --}}
    <div class="flex-shrink-0">
        <form wire:submit="sendMessage" class="flex gap-2">
            <input type="text" wire:model="message"
                   class="input-dark flex-1"
                   placeholder="Ask your coach anything..."
                   :disabled="{{ $loading ? 'true' : 'false' }}"
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
            Add <code>ANTHROPIC_API_KEY=your_key</code> to .env to enable AI coaching
        </p>
        @endif
    </div>

</div>

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
</style>

<script>
document.addEventListener('livewire:navigated', () => {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
});
window.addEventListener('scroll-to-bottom', () => {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
});
</script>
