@extends('layouts.app')

@section('title', 'About Us')
@section('body_class', 'page-gradient')

@section('content')
    <section class="container about-profile">
        <div class="about-profile__text">
            <h1>Profil</h1>
            @foreach ($company['profile'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
        <img src="{{ asset('images/logo/winnicode.png') }}" alt="WinniCode Garuda Teknologi" class="about-profile__logo" width="726" height="291">
    </section>

    <section class="about-info" id="contact">
        <div class="about-info__map">
            <iframe
                src="https://maps.google.com/maps?q={{ urlencode($company['map_query']) }}&z=16&output=embed"
                title="Lokasi {{ $company['name'] }}"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="about-info__contact">
            @include('partials.section-title', ['title' => $company['name'] . ' Information'])
            <ul class="contact-list">
                <li><i class="fa-solid fa-envelope"></i> Email : <a href="mailto:{{ $company['email'] }}">{{ $company['email'] }}</a></li>
                <li><i class="fa-solid fa-mobile-screen"></i> Phone Number : <a href="tel:{{ $company['phone'] }}">{{ $company['phone'] }}</a></li>
                <li><i class="fa-solid fa-building"></i> Bidang : {{ $company['field'] }}</li>
                <li><i class="fa-solid fa-location-dot"></i> Address : {{ $company['address'] }}</li>
            </ul>
            <div class="contact-legal">Legalitas : {{ $company['legal'] }}</div>
        </div>
    </section>
@endsection
