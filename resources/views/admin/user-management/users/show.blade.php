@extends('admin.admin_layout')

@section('title', 'User Details - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">User Details: {{ $user->name }}</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Complete user information and permissions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div class="btn-group">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                        <i class="mdi mdi-arrow-left"></i> Back to Users
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning mb-3 mb-md-0">
                        <i class="mdi mdi-pencil"></i> Edit User
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- User Information Card -->
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">User Information</h6>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                             class="rounded-circle" width="120" height="120">
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Full Name:</strong></div>
                            <div class="col-sm-9">{{ $user->name }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Email:</strong></div>
                            <div class="col-sm-9">{{ $user->email }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Status:</strong></div>
                            <div class="col-sm-9">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Joined:</strong></div>
                            <div class="col-sm-9">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Last Updated:</strong></div>
                            <div class="col-sm-9">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        
                        @if($user->email_verified_at)
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Email Verified:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge bg-success">
                                        <i class="mdi mdi-check"></i> {{ $user->email_verified_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Email Verified:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge bg-warning">
                                        <i class="mdi mdi-alert"></i> Not Verified
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Card -->
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Quick Actions</h6>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                        <i class="mdi mdi-pencil"></i> Edit User Details
                    </a>
                    
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $user->is_active ? 'secondary' : 'success' }} w-100">
                                <i class="mdi mdi-{{ $user->is_active ? 'pause' : 'play' }}"></i> 
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <i class="mdi mdi-delete"></i> Delete User
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <small><i class="mdi mdi-information"></i> This is your own account.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Roles and Permissions Card -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Assigned Roles</h6>
                
                @forelse($user->roles as $role)
                    <div class="card mb-3" style="border-left: 4px solid #4fd1c7;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $role->name }}</h6>
                                    @if($role->description)
                                        <p class="text-muted mb-2">{{ $role->description }}</p>
                                    @endif
                                    <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                                </div>
                                <span class="badge bg-primary">{{ $role->name }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i> No roles assigned to this user.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Permissions Card -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Effective Permissions</h6>
                
                @php
                    $allPermissions = $user->roles->flatMap->permissions->unique('id');
                    $groupedPermissions = $allPermissions->groupBy(function ($permission) {
                        return explode(' ', $permission->name)[1] ?? 'general';
                    });
                @endphp
                
                @if($allPermissions->count() > 0)
                    @foreach($groupedPermissions as $category => $permissions)
                        <div class="mb-3">
                            <h6 class="text-capitalize">{{ $category }}</h6>
                            <div class="permission-group">
                                @foreach($permissions as $permission)
                                    <span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i> No permissions available. Assign roles to grant permissions.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity Log (Future Enhancement) -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Account Statistics</h6>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-primary me-3">
                                <span class="mdi mdi-shield-account icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $user->roles->count() }}</h6>
                                <small class="text-muted">Roles Assigned</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-success me-3">
                                <span class="mdi mdi-key icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $allPermissions->count() }}</h6>
                                <small class="text-muted">Permissions</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-warning me-3">
                                <span class="mdi mdi-calendar icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $user->created_at->diffInDays() }}</h6>
                                <small class="text-muted">Days Since Joined</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-danger me-3">
                                <span class="mdi mdi-update icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $user->updated_at->diffInDays() }}</h6>
                                <small class="text-muted">Days Since Updated</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
