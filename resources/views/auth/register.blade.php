<x-guest-layout>
    <div class="auth-title">{{ __('Create your account') }}</div>
    <p class="auth-subtitle">{{ __('Set up your fighter profile and start training smarter.') }}</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
            <label for="name">{{ __('Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <div class="field-hint {{ $errors->has('name') ? 'has-msg' : '' }}">{{ $errors->first('name') }}</div>
        </div>

        <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
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

        <button type="submit" class="btn-submit">{{ __('Create account') }}</button>

        <p style="font-size:.72rem; color: rgba(255,255,255,.4); text-align:center; margin-top:1rem; line-height:1.55;">
            🔒 {{ __('Your data stays private — we never sell or share it.') }}<br>
            {!! __('By creating an account you agree to our :terms and :privacy.', [
                'terms'   => '<a href="'.route('terms').'" style="color: rgba(255,255,255,.62); text-decoration: underline;">'.__('Terms').'</a>',
                'privacy' => '<a href="'.route('privacy').'" style="color: rgba(255,255,255,.62); text-decoration: underline;">'.__('Privacy Policy').'</a>',
            ]) !!}
        </p>

        <div class="form-footer">
            <span>{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}" class="link-red">{{ __('Sign in') }}</a>
        </div>
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
