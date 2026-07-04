@php $mb = auth()->user(); @endphp
@if($mb)
<a href="{{ route('billing') }}" title="{{ __('Membership') }}"
   style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.35rem 0.7rem; border-radius:999px; background:rgba(255,255,255,0.04); border:1px solid var(--dark-border); font-size:0.74rem; font-weight:600; color:#d4d4dc; text-decoration:none; white-space:nowrap;">
    @if($mb->is_admin)
        👑 {{ __('Owner') }}
    @elseif($mb->subscribedActive())
        <span style="color:#2ecc71;">✓</span> {{ __('Subscribed') }}
    @elseif($mb->onTrial())
        🎁 {{ __('Trial') }} · {{ $mb->trialDaysLeft() }}{{ __('d') }}
    @else
        <span style="color:var(--blood);">🔒</span> {{ __('Subscribe') }}
    @endif
</a>
@endif
