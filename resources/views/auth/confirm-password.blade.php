<x-guest-layout>
    <div class="auth-title">{{ __('Confirm your password') }}</div>
    <p class="auth-subtitle">{{ __('This is a secure area. Please confirm your password to continue.') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <div class="field-hint {{ $errors->has('password') ? 'has-msg' : '' }}">{{ $errors->first('password') }}</div>
        </div>

        <button type="submit" class="btn-submit">{{ __('Confirm') }}</button>
    </form>
</x-guest-layout>
