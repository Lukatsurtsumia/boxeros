<x-guest-layout>
    <div class="auth-title">{{ __('Choose a new password') }}</div>
    <p class="auth-subtitle">{{ __('Almost there - set a new password for your account.') }}</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <div class="field-hint {{ $errors->has('email') ? 'has-msg' : '' }}">{{ $errors->first('email') }}</div>
        </div>

        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            <div class="pw-strength" aria-hidden="true">
                <div class="pw-bar"><div class="pw-bar-fill" id="pwBarFill"></div></div>
                <span class="pw-label" id="pwLabel"></span>
            </div>
            <div class="field-hint {{ $errors->has('password') ? 'has-msg' : '' }}">{{ $errors->first('password') }}</div>
        </div>

        <div class="field {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            <div class="field-hint {{ $errors->has('password_confirmation') ? 'has-msg' : '' }}">{{ $errors->first('password_confirmation') }}</div>
        </div>

        <button type="submit" class="btn-submit">{{ __('Reset Password') }}</button>
    </form>

    <script>
        (function () {
            var input = document.getElementById('password');
            var fill  = document.getElementById('pwBarFill');
            var label = document.getElementById('pwLabel');
            if (!input || !fill || !label) return;
            var T = { weak: @json(__('Weak')), medium: @json(__('Medium')), strong: @json(__('Strong')) };
            function update() {
                var n = input.value.length, pct, color, text;
                if (n === 0)      { pct = 0;   color = 'transparent'; text = ''; }
                else if (n < 6)   { pct = 33;  color = '#e74c3c';     text = T.weak; }
                else if (n < 12)  { pct = 66;  color = '#f39c12';     text = T.medium; }
                else              { pct = 100; color = '#2ecc71';     text = T.strong; }
                fill.style.width = pct + '%';
                fill.style.backgroundColor = color;
                label.textContent = text;
                label.style.color = color;
            }
            input.addEventListener('input', update);
            update();
        })();
    </script>
</x-guest-layout>
