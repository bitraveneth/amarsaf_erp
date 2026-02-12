<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ config('app.name', 'SAFERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <header>
                <div class="login-brand">SAFERP admin</div>
                <h1>Welcome back</h1>
                <p>Sign in to continue to your control panel.</p>
            </header>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    <button type="button" id="toggle-password" class="password-toggle" aria-label="Show password">
                        {{-- Eye icon (show) --}}
                        <svg class="password-toggle-icon password-toggle-icon-show" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5C7 5 3.1 8.1 1.5 12c1.6 3.9 5.5 7 10.5 7s8.9-3.1 10.5-7C20.9 8.1 17 5 12 5zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" fill="currentColor"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                        </svg>
                        {{-- Eye-off icon (hide) --}}
                        <svg class="password-toggle-icon password-toggle-icon-hide" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                            <path d="M3 4.3 4.3 3l17 17L20.7 21l-2.1-2.1C16.9 19.6 14.6 20.5 12 20.5 7 20.5 3.1 17.4 1.5 13c.6-1.5 1.6-2.9 2.8-4L3 4.3zm5.2 5.2A4 4 0 0 0 12 16a4 4 0 0 0 2.5-.9l-6.3-6.3zM12 5c2.6 0 4.9.9 6.6 2.6 1.2 1.1 2.1 2.5 2.9 4-.4 1-1 2-1.7 2.9l-2-2A5.9 5.9 0 0 0 18.9 12C17.3 8.8 14.8 7 12 7c-.7 0-1.4.1-2 .3L8.2 5.5C9.4 5.2 10.7 5 12 5z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <div class="form-remember">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit">Sign in</button>
            </form>
        </div>
    </div>

</body>
</html>
