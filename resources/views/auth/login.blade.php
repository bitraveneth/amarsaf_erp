@extends('layouts.auth')

@section('title', 'Sign in | ' . ($appBrandName ?? config('app.name')))

@section('content')
<div class="auth-form w-full">
    <header class="auth-form__head">
        <h1 class="auth-form__title">
            {{ app()->getLocale() === 'bn' ? 'আবার স্বাগতম' : 'Welcome back' }}
        </h1>
        <p class="auth-form__subtitle">
            {{ app()->getLocale() === 'bn'
                ? 'আপনার ইমেইল ও পাসওয়ার্ড দিয়ে অ্যাডমিন প্যানেলে প্রবেশ করুন।'
                : 'Enter your email and password to access your admin panel.' }}
        </p>
    </header>

    <form method="POST" action="{{ route('login') }}" class="auth-form__body">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-field__label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="you@company.com"
                class="auth-field__input auth-field__input--plain"
            />
            @error('email')
                <p class="auth-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password" class="auth-field__label">Password</label>
            <div class="auth-field__control">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="auth-field__input auth-field__input--plain auth-field__input--password"
                />
                <button type="button" class="auth-field__toggle" aria-label="Show password" data-password-toggle>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="2.5"/></svg>
                </button>
            </div>
            @error('password')
                <p class="auth-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__meta">
            <label class="auth-remember">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                <span>{{ app()->getLocale() === 'bn' ? 'আমাকে মনে রাখুন' : 'Remember me' }}</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-field__link">
                    {{ app()->getLocale() === 'bn' ? 'পাসওয়ার্ড ভুলে গেছেন?' : 'Forgot password?' }}
                </a>
            @endif
        </div>

        <button type="submit" class="auth-submit auth-submit--solid">
            {{ app()->getLocale() === 'bn' ? 'লগ ইন' : 'Log in' }}
        </button>

        @if(app()->environment('local'))
            <div class="auth-demo">
                <p class="auth-demo__label">Demo logins <span class="auth-demo__hint">local only</span></p>
                <div class="auth-demo__chips">
                    @foreach([
                        'super@saferpv.local' => 'Super',
                        'admin@saferpv.local' => 'Admin',
                        'accounts@saferpv.local' => 'Accounts',
                        'warehouse@saferpv.local' => 'Warehouse',
                    ] as $email => $label)
                        <button type="button" class="auth-demo__chip" data-demo-login data-email="{{ $email }}" data-password="password">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
        if (!passwordInput) return;
        const show = passwordInput.type === 'password';
        passwordInput.type = show ? 'text' : 'password';
        this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });

    document.querySelectorAll('[data-demo-login]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (emailInput) emailInput.value = this.dataset.email || '';
            if (passwordInput) passwordInput.value = this.dataset.password || '';
            emailInput?.focus();
        });
    });

    if (window.innerWidth < 768 && emailInput) {
        emailInput.removeAttribute('autofocus');
    }
});
</script>
@endpush
