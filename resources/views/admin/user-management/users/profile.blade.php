@extends('admin.admin_layout')
@section('title', 'Edit Profile')
@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <h2 class="mb-4">Update Your Profile</h2>
        <div class="card mb-4">
            <div class="card-body">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <form method="POST" action="{{ route('user.profile-photo.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 text-center">
                            <img src="{{ Auth::user()->profile_photo_url }}" class="rounded-circle mb-2" width="96" height="96" alt="Profile Photo">
                            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
                            @error('photo')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Update Photo</button>
                    </form>
                    <hr>
                @endif
                @livewire('profile.update-profile-information-form')
                <x-jet-section-border />
                @livewire('profile.update-password-form')
            </div>
        </div>
    </div>
</div>
@endsection
