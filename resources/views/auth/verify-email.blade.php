<x-guest-layout>
    <div class="auth-title">{{ __('Verify your email') }}</div>
    <p class="auth-subtitle">{{ __("Welcome to the corner. We've sent a verification link to your inbox — tap it to activate your account and step inside. Didn't get it? We'll send a fresh one.") }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="session-status">{{ __('A new verification link is on its way to your inbox.') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-submit">{{ __('Resend Verification Email') }}</button>
    </form>

    <div class="form-footer" style="justify-content: center;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="background: none; border: none; cursor: pointer; font-family: 'Inter', sans-serif; font-size: .8rem; color: var(--muted); transition: color .2s;"
                    onmouseover="this.style.color='#f0f0f0'" onmouseout="this.style.color='var(--muted)'">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
