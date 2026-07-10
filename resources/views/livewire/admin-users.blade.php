<div>
    <h1 class="font-display text-3xl font-bold">{{ __('Users') }}</h1>
    <p style="color: var(--text-muted); font-size:.85rem; margin-bottom:1.25rem;">{{ __('Everyone registered on BoxerOS.') }}</p>

    {{-- Visitor stats (people who came to the site - registered or not) --}}
    <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:.5rem;">👁 {{ __('Visitors') }}</div>
    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:.7rem; margin-bottom:.5rem;">
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num" style="color:#3498db;">{{ $visitorsToday }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('Today') }}</div>
        </div>
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num" style="color:#3498db;">{{ $visitors7d }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('Last 7 days') }}</div>
        </div>
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num" style="color:#3498db;">{{ $visitorsTotal }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('All time') }}</div>
        </div>
    </div>
    <p style="color:var(--text-muted); font-size:.68rem; margin-bottom:1.25rem;">{{ __('Unique visitors (bots excluded). Counting starts from today.') }}</p>

    {{-- Registered-user stats --}}
    <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:.5rem;">🥊 {{ __('Registered') }}</div>
    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:.7rem; margin-bottom:1.25rem;">
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num">{{ $total }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('Total') }}</div>
        </div>
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num" style="color:var(--gold);">{{ $trialing }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('On trial') }}</div>
        </div>
        <div class="card" style="text-align:center; padding:1rem;">
            <div class="stat-num" style="color:#2ecc71;">{{ $subscribers }}</div>
            <div style="color:var(--text-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.5px;">{{ __('Subscribers') }}</div>
        </div>
    </div>

    {{-- Search --}}
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search name or email…') }}"
        style="width:100%; padding:.7rem 1rem; margin-bottom:1rem; background:var(--dark-card); border:1px solid var(--dark-border); border-radius:12px; color:var(--text-primary); font-size:.9rem; outline:none;">

    {{-- User list --}}
    <div style="display:flex; flex-direction:column; gap:.6rem;">
        @forelse($users as $u)
            <div class="card" style="display:flex; align-items:center; gap:.9rem; flex-wrap:wrap; padding:1rem 1.1rem;">
                <div style="flex:1; min-width:180px;">
                    <div style="font-weight:600; display:flex; align-items:center; gap:.45rem; flex-wrap:wrap;">
                        <span>{{ $u->name }}</span>
                        @if($u->is_admin)
                            <span style="background:rgba(243,156,18,.15); color:var(--gold); border:1px solid rgba(243,156,18,.3); padding:.08rem .5rem; border-radius:999px; font-size:.62rem; font-weight:700;">👑 {{ __('Admin') }}</span>
                        @endif
                        @if($u->google_id)
                            <span style="background:rgba(66,133,244,.15); color:#4285F4; border:1px solid rgba(66,133,244,.3); padding:.08rem .5rem; border-radius:999px; font-size:.62rem; font-weight:700;">Google</span>
                        @endif
                        @if($u->password)
                            <span style="background:rgba(255,255,255,.06); color:var(--text-muted); border:1px solid var(--dark-border); padding:.08rem .5rem; border-radius:999px; font-size:.62rem; font-weight:600;">{{ __('Password') }}</span>
                        @endif
                    </div>
                    <div style="color:var(--text-muted); font-size:.8rem; overflow:hidden; text-overflow:ellipsis;">{{ $u->email }}</div>
                    <div style="color:var(--text-muted); font-size:.72rem; margin-top:.25rem;">
                        {{ __('Joined') }} {{ $u->created_at?->format('d M Y') }}
                        @if($u->subscribedActive())
                            · <span style="color:#2ecc71;">{{ __('Subscribed') }}</span>
                        @elseif($u->onTrial())
                            · <span style="color:var(--gold);">{{ __('Trial') }} · {{ $u->trialDaysLeft() }}{{ __('d') }}</span>
                        @else
                            · <span>{{ __('Free') }}</span>
                        @endif
                    </div>
                </div>

                @if($u->id !== auth()->id())
                    <button wire:click="deleteUser({{ $u->id }})"
                        wire:confirm="{{ __('Delete :name and ALL their data? This cannot be undone.', ['name' => $u->name]) }}"
                        style="background:rgba(192,57,43,.15); border:1px solid rgba(192,57,43,.4); color:#e74c3c; border-radius:10px; padding:.5rem .85rem; font-size:.8rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                        🗑 {{ __('Delete') }}
                    </button>
                @else
                    <span style="color:var(--text-muted); font-size:.72rem; white-space:nowrap;">{{ __('You') }}</span>
                @endif
            </div>
        @empty
            <div class="card" style="text-align:center; color:var(--text-muted); padding:2rem;">{{ __('No users found.') }}</div>
        @endforelse
    </div>

    @if(count($users) === 100)
        <p style="color:var(--text-muted); font-size:.72rem; text-align:center; margin-top:1rem;">{{ __('Showing the 100 most recent - use search to find others.') }}</p>
    @endif
</div>
