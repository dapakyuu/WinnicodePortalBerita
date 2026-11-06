@extends('admin.layouts.app')

@php $editing = $user->exists; @endphp

@section('title', $editing ? 'Edit User' : 'Tambah User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User</a></li>
    <li class="breadcrumb-item active">{{ $editing ? 'Edit' : 'Baru' }}</li>
@endsection

@section('content')
    <form action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-danger">
                    <div class="card-header"><h3 class="card-title">Data akun</h3></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Nama <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="50" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" maxlength="50" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="50" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="role">Role <span class="text-danger">*</span></label>
                                <select id="role" name="role" class="form-control @error('role') is-invalid @enderror">
                                    <option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>User</option>
                                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password {!! $editing ? '' : '<span class="text-danger">*</span>' !!}</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @required(! $editing)>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="form-text text-muted">{{ $editing ? 'Kosongkan jika tidak ingin mengganti.' : 'Minimal 8 karakter.' }}</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Ulangi password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4" maxlength="1000" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Foto</h3></div>
                    <div class="card-body">
                        <div class="form-group text-center">
                            <img src="{{ $user->avatar_url }}" alt="" class="img-circle elevation-1 mb-2 avatar-preview" data-preview-for="avatar">
                            <div class="custom-file text-left">
                                <input type="file" class="custom-file-input @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/*">
                                <label class="custom-file-label" for="avatar">Avatar...</label>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <img src="{{ $user->banner_url }}" alt="" class="img-fluid rounded mb-2 image-preview" data-preview-for="banner">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('banner') is-invalid @enderror" id="banner" name="banner" accept="image/*">
                                <label class="custom-file-label" for="banner">Banner profil...</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-danger ml-auto"><i class="fas fa-floppy-disk"></i> Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
