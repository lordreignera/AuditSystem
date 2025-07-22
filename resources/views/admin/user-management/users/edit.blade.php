@extends('admin.admin_layout')

@section('title', 'Edit User - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Edit User: {{ $user->name }}</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Update user information and role assignments</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">User Information</h6>
                
                <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                        @error('profile_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Upload a new profile photo to replace the current one.</div>
                    </div>
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to keep current password.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Assign Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               value="{{ $role->name }}" id="role_{{ $role->id }}" 
                                               name="roles[]" 
                                               {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            <strong>{{ $role->name }}</strong>
                                            @if($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-content-save"></i> Update User
                            </button>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info me-2">
                                <i class="mdi mdi-eye"></i> View User
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">User Details</h6>
                
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                         class="rounded-circle me-3" width="64" height="64">
                    <div>
                        <h6 class="mb-0">{{ $user->name }}</h6>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Status:</small>
                        <p class="mb-0">
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Joined:</small>
                        <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Current Roles:</small>
                    @forelse($user->roles as $role)
                        <span class="badge bg-primary me-1">{{ $role->name }}</span>
                    @empty
                        <p class="mb-0 text-muted">No roles assigned</p>
                    @endforelse
                </div>
                
                @if($user->id !== auth()->id())
                    <div class="alert alert-warning">
                        <small><i class="mdi mdi-alert"></i> You can modify this user's roles and permissions.</small>
                    </div>
                @else
                    <div class="alert alert-info">
                        <small><i class="mdi mdi-information"></i> This is your own account. Be careful when modifying roles.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
