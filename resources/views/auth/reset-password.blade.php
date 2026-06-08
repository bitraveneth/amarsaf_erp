@extends('layouts.auth')

@section('title', 'Reset password | ' . ($appBrandName ?? config('app.name')))

@section('content')
<div class="auth-form w-full">
    <header class="auth-form__head">
        <h1 class="auth-form__title">
            {{ app()->getLocale() === 'bn' ? 'নতুন পাসওয়ার্ড' : 'Choose a new password' }}
        </h1>
        <p class="auth-form__subtitle">
            {{ app()->getLocale() === 'bn'
                ? 'আপনার অ্যাকাউন্টের জন্য একটি নতুন পাসওয়ার্ড সেট করুন।'
                : 'Set a new password for your account.' }}
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="auth-form__body">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="email" class="auth-field__label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                autocomplete="email"
                placeholder="you@company.com"
                class="auth-field__input auth-field__input--plain"
            />
            @error('email')
                <p class="auth-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password" class="auth-field__label">
                {{ app()->getLocale() === 'bn' ? 'নতুন পাসওয়ার্ড' : 'New password' }}
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="auth-field__input auth-field__input--plain"
            />
            @error('password')
                <p class="auth-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation" class="auth-field__label">
                {{ app()->getLocale() === 'bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm password' }}
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="auth-field__input auth-field__input--plain"
            />
        </div>

        <button type="submit" class="auth-submit auth-submit--solid">
            {{ app()->getLocale() === 'bn' ? 'পাসওয়ার্ড আপডেট' : 'Update password' }}
        </button>

        <p class="auth-form__back">
            <a href="{{ route('login') }}" class="auth-field__link">
                {{ app()->getLocale() === 'bn' ? 'লগ ইনে ফিরে যান' : 'Back to sign in' }}
            </a>
        </p>
    </form>
</div>
@endsection
