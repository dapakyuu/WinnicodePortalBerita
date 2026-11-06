{{-- Kepala halaman profil: banner, avatar, nama, tab menu, tombol Edit Profile. Parameter: $user, $active --}}
<section class="container profile-top">
    <img src="{{ $user->banner_url }}" alt="" class="profile-top__banner">
    <div class="profile-top__bar">
        <div class="profile-top__user">
            <img src="{{ $user->avatar_url }}" alt="" width="75" height="75">
            <span>{{ $user->name }}</span>
        </div>
        <nav class="profile-tabs" aria-label="Menu profil">
            <a href="{{ route('profile.marked') }}" class="{{ $active === 'marked' ? 'is-active' : '' }}">Marked</a>
            <a href="{{ route('profile.create') }}" class="{{ $active === 'send' ? 'is-active' : '' }}">Send Post</a>
            <a href="{{ route('profile.posts') }}" class="{{ $active === 'posts' ? 'is-active' : '' }}">Posts</a>
        </nav>
        <a href="{{ route('profile.edit') }}" class="btn-edit-profile"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
    </div>
</section>
