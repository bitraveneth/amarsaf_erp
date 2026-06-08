@extends('layouts.auth')

@section('title', 'Forgot password | ' . ($appBrandName ?? config('app.name')))

@section('content')
<div class="auth-form w-full">
    <header class="auth-form__head">
        <h1 class="auth-form__title">Reset password</h1>
        <p class="auth-form__subtitle">Enter your email and we will send you a reset link.</p>
    </header>

    @if (session('status'))
        <div class="auth-form__alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form__body">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-field__label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@company.com" class="auth-field__input auth-field__input--plain" />
            @error('email')
                <p class="auth-field__error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-submit auth-submit--solid">Send reset link</button>

        <p class="auth-form__back">
            <a href="{{ route('login') }}" class="auth-field__link">Back to sign in</a>
        </p>
    </form>
</div>
@endsection
