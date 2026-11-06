@extends('layouts.app')

@section('title', 'Profile - Edit')
@section('body_class', 'page-gradient')

@section('content')
    <section class="container profile-edit">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="edit-form" novalidate>
            @csrf
            @method('PUT')
            <div class="edit-form__row edit-form__row--top">
                @include('partials.input', ['name' => 'name', 'label' => 'Name', 'value' => $user->name, 'class' => 'field--grow'])
                @include('partials.input', ['name' => 'username', 'label' => 'User Name', 'value' => $user->username])
            </div>
            <div class="edit-form__row">
                @include('partials.input', ['name' => 'old_password', 'label' => 'Old Password', 'type' => 'password', 'autocomplete' => 'current-password', 'placeholder' => 'Isi bila ingin ganti password'])
                @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'autocomplete' => 'new-password', 'placeholder' => 'Password baru (min. 8 karakter)'])
                @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $user->email])
            </div>

            @include('partials.dropzone', ['name' => 'banner', 'label' => 'Add Banner', 'wide' => true, 'current' => $user->banner ? $user->banner_url : null])

            <div class="edit-form__row edit-form__row--content">
                @include('partials.editor', ['name' => 'bio', 'label' => 'Explanation', 'value' => $user->bio])
                @include('partials.dropzone', ['name' => 'avatar', 'label' => 'Add Image', 'current' => $user->avatar ? $user->avatar_url : null])
            </div>

            <div class="edit-form__actions">
                <button type="submit" class="btn-solid"><i class="fa-regular fa-floppy-disk"></i> Save</button>
            </div>
        </form>
    </section>
@endsection
