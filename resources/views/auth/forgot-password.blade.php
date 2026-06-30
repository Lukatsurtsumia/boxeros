<x-guest-layout>
    <div class="auth-title">{{ __('Reset your password') }}</div>
    <p class="auth-subtitle">{{ __("Enter your email and we'll send you a link to choose a new password.") }}</p>

    @if (session('status'))
        <div class="session-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <div class="field-hint {{ $errors->has('email') ? 'has-msg' : '' }}">{{ $errors->first('email') }}</div>
        </div>

        <button type="submit" class="btn-submit">{{ __('Email Password Reset Link') }}</button>

        <div class="form-footer" style="justify-content: center;">
            <a href="{{ route('login') }}">← {{ __('Back to sign in') }}</a>
        </div>
    </form>
</x-guest-layout>
