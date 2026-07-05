<x-guest-layout>
    <div class="auth-title">{{ __('Verify your email') }}</div>
    <p class="auth-subtitle">{{ __('We sent a 6-digit code to :email. Enter it below to activate your account.', ['email' => auth()->user()->email]) }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="session-status">{{ __('A fresh code is on its way to your inbox.') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.code') }}">
        @csrf
        <div class="field {{ $errors->has('code') ? 'has-error' : '' }}">
            <label for="code">{{ __('Verification code') }}</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                   maxlength="6" pattern="[0-9]*" required autofocus
                   style="letter-spacing:.6rem; text-align:center; font-size:1.5rem; font-family:'Rajdhani',sans-serif; font-weight:700;"
                   placeholder="––––––">
            <div class="field-hint {{ $errors->has('code') ? 'has-msg' : '' }}">{{ $errors->first('code') }}</div>
        </div>

        <button type="submit" class="btn-submit">{{ __('Verify') }}</button>
    </form>

    <div class="form-footer">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    style="background:none; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:.8rem; color:var(--muted); transition:color .2s;"
                    onmouseover="this.style.color='#f0f0f0'" onmouseout="this.style.color='var(--muted)'">
                {{ __('Resend code') }}
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="background:none; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:.8rem; color:var(--muted); transition:color .2s;"
                    onmouseover="this.style.color='#f0f0f0'" onmouseout="this.style.color='var(--muted)'">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
