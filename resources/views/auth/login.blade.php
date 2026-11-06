@extends('layouts.app')

@section('title', 'Login')
@section('body_class', 'page-gradient')

@section('content')
    <section class="container auth auth--login">
        <h1 class="auth__title">Sign in to your account</h1>
        <form action="{{ route('login') }}" method="POST" class="auth__form" novalidate>
            @csrf
            @include('partials.input', ['name' => 'login', 'label' => 'Username / Email', 'placeholder' => 'Enter your username or email', 'autocomplete' => 'username'])
            @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'placeholder' => 'Enter your password', 'autocomplete' => 'current-password'])
            <button type="submit" class="btn-pill">Login</button>
            <p class="auth__switch">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
        </form>
    </section>
@endsection
