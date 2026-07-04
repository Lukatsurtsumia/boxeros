<div class="corner-shell" style="height: calc(100vh - 130px);">

    {{-- ═══════════ Header ═══════════ --}}
    <div class="corner-header">
        <div class="flex items-center gap-3">
            <div class="corner-coach-av">
                🥊
                <span class="corner-online"></span>
            </div>
            <div>
                <div class="corner-name" translate="no">CORNER <span class="corner-live">● {{ __('Live') }}</span></div>
                <div class="corner-sub">{{ __('Your AI corner man') }}</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="startSession" class="corner-hbtn" title="{{ __('New session') }}">🎯</button>
            <button wire:click="clearChat" wire:confirm="{{ __('Clear all chat history?') }}" class="corner-hbtn" title="{{ __('Clear') }}">🧹</button>
        </div>
    </div>

    {{-- ═══════════ Messages ═══════════ --}}
    <div class="corner-messages" id="chat-messages" x-data="{}" @scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight">

        @if($messages->count() === 0)
        <div class="corner-empty">
            <div class="corner-empty-glove">🥊</div>
            <div class="corner-empty-title">{{ __('Step into the corner') }}</div>
            <p class="corner-empty-sub">{{ __("I know your profile, today's numbers, meals and next fight. Ask me anything — or start a full coaching session.") }}</p>
            <button type="button" wire:click="startSession" class="corner-cta">🎯 {{ __('Start a coaching session') }}</button>
            <div class="corner-or"><span>{{ __('or tap a question') }}</span></div>
            <div class="corner-suggest-grid">
                @foreach([
                    ['⚖️', __('How should I cut weight for my next fight?')],
                    ['🤕', __('My shoulder hurts after training. What should I do?')],
                    ['🍽️', __('Give me a meal plan for fight week')],
                    ['💧', __('How am I doing with my water intake?')],
                    ['😮‍💨', __('I feel exhausted today. Is my training too intense?')],
                ] as $s)
                <button type="button" wire:click="ask('{{ addslashes($s[1]) }}')" wire:loading.attr="disabled" class="corner-suggest">
                    <span class="corner-suggest-emoji">{{ $s[0] }}</span>
                    <span>{{ $s[1] }}</span>
                    <span class="corner-suggest-arrow">→</span>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        @foreach($messages as $msg)
            @if($msg->role === 'assistant')
            <div class="corner-row corner-row-ai" wire:key="m{{ $msg->id }}">
                <div class="corner-msg-av">🥊</div>
                <div class="corner-bubble corner-bubble-ai">
                    <div class="chat-markdown">{!! \Illuminate\Support\Str::markdown($msg->content, ['html_input' => 'strip', 'allow_unsafe_links' => false, 'renderer' => ['soft_break' => '<br>']]) !!}</div>
                    <div class="corner-time">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @else
            <div class="corner-row corner-row-user" wire:key="m{{ $msg->id }}">
                <div class="corner-bubble corner-bubble-user">
                    {{ $msg->content }}
                    <div class="corner-time corner-time-user">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @endif
        @endforeach

        @if($loading)
        <div class="corner-row corner-row-ai">
            <div class="corner-msg-av">🥊</div>
            <div class="corner-bubble corner-bubble-ai corner-typing">
                <span class="corner-dot"></span><span class="corner-dot"></span><span class="corner-dot"></span>
                <span class="corner-typing-label">{{ __('CORNER is thinking') }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════ Input ═══════════ --}}
    <div class="corner-input-wrap">
        <form wire:submit="sendMessage" class="corner-inputbar">
            <input type="text" wire:model="message" id="corner-input" class="corner-input"
                   placeholder="{{ __('Ask your coach anything…') }}" {{ $loading ? 'disabled' : '' }} autocomplete="off">
            <button type="submit" class="corner-send" {{ $loading ? 'disabled' : '' }} aria-label="{{ __('Send') }}">
                <span wire:loading.remove wire:target="sendMessage">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </span>
                <span wire:loading wire:target="sendMessage" class="corner-send-spin">⏳</span>
            </button>
        </form>
        @if(!config('services.anthropic.key'))
        <p class="corner-note" style="color: var(--blood);">{!! __('Add <code>ANTHROPIC_API_KEY=your_key</code> to .env to enable AI coaching') !!}</p>
        @elseif($chatRemaining !== null)
        <p class="corner-note" style="color: {{ $chatRemaining <= 3 ? 'var(--blood)' : 'var(--text-muted)' }};">
            @if($chatRemaining > 0)
                <span class="corner-note-pill">{{ $chatRemaining }} {{ $chatRemaining === 1 ? __('coaching message left today') : __('coaching messages left today') }}</span>
            @else
                {{ __('Daily coaching limit reached — back tomorrow') }}
            @endif
        </p>
        @endif
    </div>

</div>

<style>
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
@keyframes cornerIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
@keyframes cornerFloat { 0%, 100% { transform: translateY(0) rotate(-6deg); } 50% { transform: translateY(-9px) rotate(6deg); } }
@keyframes cornerPulse { 0% { box-shadow: 0 0 0 0 rgba(46,204,113,0.5); } 70% { box-shadow: 0 0 0 6px rgba(46,204,113,0); } 100% { box-shadow: 0 0 0 0 rgba(46,204,113,0); } }

.corner-shell { display: flex; flex-direction: column; }

/* Header */
.corner-header { display: flex; align-items: center; justify-content: space-between;
    padding: 0.7rem 0.9rem; margin-bottom: 0.75rem; border-radius: 16px;
    background: linear-gradient(135deg, rgba(192,57,43,0.14), rgba(20,21,26,0.6));
    border: 1px solid var(--dark-border); }
.corner-coach-av { position: relative; width: 46px; height: 46px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
    background: linear-gradient(145deg, var(--blood-dark), var(--blood));
    box-shadow: 0 4px 16px rgba(192,57,43,0.4); }
.corner-online { position: absolute; bottom: -2px; right: -2px; width: 13px; height: 13px;
    border-radius: 50%; background: #2ecc71; border: 2px solid #0b0c0e; animation: cornerPulse 2s infinite; }
.corner-name { font-family: 'Rajdhani', sans-serif; font-size: 1.25rem; font-weight: 700; line-height: 1;
    display: flex; align-items: center; gap: 0.5rem; }
.corner-live { font-size: 0.62rem; font-weight: 700; letter-spacing: 0.05em; color: #2ecc71;
    background: rgba(46,204,113,0.12); padding: 0.12rem 0.45rem; border-radius: 999px; text-transform: uppercase; }
.corner-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }
.corner-hbtn { width: 36px; height: 36px; border-radius: 11px; border: 1px solid var(--dark-border);
    background: rgba(255,255,255,0.03); font-size: 0.95rem; cursor: pointer; transition: all .15s;
    display: flex; align-items: center; justify-content: center; }
.corner-hbtn:hover { background: rgba(255,255,255,0.08); transform: translateY(-1px); }

/* Messages area */
.corner-messages { flex: 1; overflow-y: auto; padding: 0.25rem 0.35rem 0.5rem; margin-bottom: 0.75rem;
    display: flex; flex-direction: column; gap: 0.7rem; }
.corner-messages::-webkit-scrollbar { width: 6px; }
.corner-messages::-webkit-scrollbar-thumb { background: var(--dark-border); border-radius: 3px; }

.corner-row { display: flex; align-items: flex-end; gap: 0.5rem; animation: cornerIn .28s ease both; }
.corner-row-ai { justify-content: flex-start; }
.corner-row-user { justify-content: flex-end; }
.corner-msg-av { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; margin-bottom: 2px;
    display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
    background: linear-gradient(145deg, var(--blood-dark), var(--blood)); box-shadow: 0 2px 8px rgba(192,57,43,0.35); }

.corner-bubble { max-width: 86%; padding: 0.7rem 0.9rem; font-size: 0.9rem; line-height: 1.5;
    position: relative; word-wrap: break-word; }
.corner-bubble-ai { background: #15161c; border: 1px solid var(--dark-border); color: #e8e8ee;
    border-radius: 16px 16px 16px 4px; }
.corner-bubble-user { background: linear-gradient(135deg, var(--blood), var(--blood-dark)); color: #fff;
    border-radius: 16px 16px 4px 16px; box-shadow: 0 3px 12px rgba(192,57,43,0.3); }
.corner-time { font-size: 0.62rem; margin-top: 0.35rem; color: rgba(255,255,255,0.28); text-align: right; }
.corner-time-user { color: rgba(255,255,255,0.55); }

/* Typing indicator */
.corner-typing { display: flex; align-items: center; gap: 4px; }
.corner-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blood);
    display: inline-block; animation: bounce 1s infinite; }
.corner-dot:nth-child(2) { animation-delay: .2s; } .corner-dot:nth-child(3) { animation-delay: .4s; }
.corner-typing-label { font-size: 0.72rem; color: var(--text-muted); margin-left: 0.4rem; }

/* Empty state */
.corner-empty { text-align: center; padding: 1.5rem 0.5rem; }
.corner-empty-glove { font-size: 3.4rem; display: inline-block; animation: cornerFloat 3.5s ease-in-out infinite;
    filter: drop-shadow(0 6px 14px rgba(192,57,43,0.4)); }
.corner-empty-title { font-family: 'Rajdhani', sans-serif; font-size: 1.5rem; font-weight: 700; margin: 0.75rem 0 0.35rem; }
.corner-empty-sub { font-size: 0.85rem; line-height: 1.6; color: var(--text-muted); max-width: 22rem; margin: 0 auto 1.15rem; }
.corner-cta { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    background: linear-gradient(135deg, var(--blood), var(--blood-dark)); color: #fff; font-weight: 700;
    border: none; cursor: pointer; padding: 0.75rem 1.5rem; border-radius: 12px; font-size: 0.9rem;
    box-shadow: 0 4px 16px rgba(192,57,43,0.35); transition: transform .15s; }
.corner-cta:hover { transform: translateY(-2px); }
.corner-or { position: relative; margin: 1.15rem 0; color: var(--text-muted); font-size: 0.72rem; }
.corner-or::before, .corner-or::after { content: ""; position: absolute; top: 50%; width: 28%; height: 1px; background: var(--dark-border); }
.corner-or::before { left: 4%; } .corner-or::after { right: 4%; }
.corner-suggest-grid { display: flex; flex-direction: column; gap: 0.5rem; text-align: left; }
.corner-suggest { display: flex; align-items: center; gap: 0.65rem; width: 100%; cursor: pointer;
    padding: 0.7rem 0.85rem; border-radius: 13px; font-size: 0.85rem; color: #d4d4dc;
    background: rgba(255,255,255,0.035); border: 1px solid var(--dark-border); transition: all .15s; }
.corner-suggest:hover { background: rgba(192,57,43,0.1); border-color: rgba(192,57,43,0.4); transform: translateX(3px); }
.corner-suggest-emoji { font-size: 1.15rem; flex-shrink: 0; }
.corner-suggest-arrow { margin-left: auto; color: var(--blood); opacity: 0; transition: opacity .15s; }
.corner-suggest:hover .corner-suggest-arrow { opacity: 1; }

/* Input bar */
.corner-input-wrap { flex-shrink: 0; }
.corner-inputbar { display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.35rem 0.35rem 1rem;
    background: #15161c; border: 1px solid var(--dark-border); border-radius: 999px; transition: border-color .15s; }
.corner-inputbar:focus-within { border-color: rgba(192,57,43,0.5); }
.corner-input { flex: 1; background: transparent; border: none; outline: none; color: #fff;
    font-size: 0.9rem; padding: 0.55rem 0; }
.corner-input::placeholder { color: var(--text-muted); }
.corner-send { width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; color: #fff;
    background: linear-gradient(135deg, var(--blood), var(--blood-dark));
    box-shadow: 0 3px 12px rgba(192,57,43,0.4); transition: transform .15s; }
.corner-send:hover:not(:disabled) { transform: scale(1.06); }
.corner-send:disabled { opacity: 0.6; cursor: default; }
.corner-note { font-size: 0.72rem; text-align: center; margin-top: 0.6rem; }
.corner-note-pill { background: rgba(255,255,255,0.05); padding: 0.2rem 0.7rem; border-radius: 999px; }

/* Rendered markdown inside CORNER's replies */
.chat-markdown > *:first-child { margin-top: 0; }
.chat-markdown > *:last-child { margin-bottom: 0; }
.chat-markdown p { margin: 0 0 0.6rem; }
.chat-markdown h1, .chat-markdown h2, .chat-markdown h3, .chat-markdown h4 { font-weight: 700; line-height: 1.25; margin: 0.85rem 0 0.45rem; }
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
