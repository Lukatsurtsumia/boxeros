<x-guest-layout>
    <div class="auth-title">{{ __('Sign in') }}</div>
    <p class="auth-subtitle">{{ __('Welcome back — step into your corner.') }}</p>

    @if (session('status'))
        <div class="session-status">{{ session('status') }}</div>
    @endif

    @include('auth._google')

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <div class="field-hint {{ $errors->has('email') ? 'has-msg' : '' }}">{{ $errors->first('email') }}</div>
        </div>

        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <div class="field-hint {{ $errors->has('password') ? 'has-msg' : '' }}">{{ $errors->first('password') }}</div>
        </div>

        <div class="check-row">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">{{ __('Remember me') }}</label>
        </div>

        <button type="submit" class="btn-submit">{{ __('Sign in') }}</button>

        <div class="form-footer">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
            <a href="{{ route('register') }}" class="link-red">{{ __('Create an account') }}</a>
        </div>
    </form>
</x-guest-layout>
