@extends('layouts.app')

@section('title', 'Register')
@section('body_class', 'page-gradient')

@section('content')
    <section class="container auth auth--register">
        <h1 class="auth__title">User Registration</h1>
        <form action="{{ route('register') }}" method="POST" class="auth__form" novalidate>
            @csrf
            @include('partials.input', ['name' => 'name', 'label' => 'Nama', 'autocomplete' => 'name'])
            @include('partials.input', ['name' => 'username', 'label' => 'Username', 'autocomplete' => 'username'])
            @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'autocomplete' => 'email'])
            @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'autocomplete' => 'new-password'])
            @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'type' => 'password', 'autocomplete' => 'new-password'])
            <button type="submit" class="btn-pill">Register</button>
            <p class="auth__switch">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
        </form>
    </section>
@endsection
