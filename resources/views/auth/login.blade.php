<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ config('app.name', 'SaferPV') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <header>
                <h1>Welcome back</h1>
                <p>Sign in to continue to the SaferPV admin cockpit.</p>
            </header>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
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
