@extends('admin.admin_layout')

@section('title', 'Role Details - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Role: {{ $role->name }}</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Complete role information and permissions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div class="btn-group">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                        <i class="mdi mdi-arrow-left"></i> Back to Roles
                    </a>
                    @if($role->name !== 'Super Admin')
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning mb-3 mb-md-0">
                            <i class="mdi mdi-pencil"></i> Edit Role
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Role Information Card -->
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Information</h6>
                
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="icon icon-box-primary" style="width: 80px; height: 80px;">
                            <span class="mdi mdi-shield-account" style="font-size: 40px;"></span>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Role Name:</strong></div>
                            <div class="col-sm-9">
                                {{ $role->name }}
                                @if($role->name === 'Super Admin')
                                    <span class="badge bg-danger ms-2">System Role</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Description:</strong></div>
                            <div class="col-sm-9">{{ $role->description ?? 'No description provided' }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Permissions:</strong></div>
                            <div class="col-sm-9">
                                <span class="badge bg-info">{{ $role->permissions->count() }} permissions assigned</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Users:</strong></div>
                            <div class="col-sm-9">
                                <span class="badge bg-success">{{ $role->users->count() }} users have this role</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Created:</strong></div>
                            <div class="col-sm-9">{{ $role->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Last Updated:</strong></div>
                            <div class="col-sm-9">{{ $role->updated_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
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
                    @if($role->name !== 'Super Admin')
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                            <i class="mdi mdi-pencil"></i> Edit Role Details
                        </a>
                        
                        @if($role->users->count() === 0)
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" 
                                        onclick="return confirm('Are you sure you want to delete this role? This action cannot be undone.')">
                                    <i class="mdi mdi-delete"></i> Delete Role
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger w-100" disabled title="Cannot delete role assigned to users">
                                <i class="mdi mdi-lock"></i> Role Has Users
                            </button>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <small><i class="mdi mdi-information"></i> System role cannot be modified.</small>
                        </div>
                    @endif
                    
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="mdi mdi-account-plus"></i> Create User with This Role
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Permissions Card -->
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Permissions</h6>
                
                @php
                    $groupedPermissions = $role->permissions->groupBy(function ($permission) {
                        return explode(' ', $permission->name)[1] ?? 'general';
                    });
                @endphp
                
                @if($role->permissions->count() > 0)
                    @foreach($groupedPermissions as $category => $permissions)
                        <div class="permission-category mb-4">
                            <h6 class="text-capitalize text-primary">
                                <i class="mdi mdi-folder"></i> {{ str_replace('_', ' ', $category) }} Permissions
                            </h6>
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            <span class="badge bg-primary">{{ $permission->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i> No permissions assigned to this role.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Users with this Role Card -->
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Users with this Role</h6>
                
                @if($role->users->count() > 0)
                    <div class="list-group">
                        @foreach($role->users as $user)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                                         class="rounded-circle me-2" width="32" height="32">
                                    <div>
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-sm btn-outline-info" title="View User">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit User">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i> No users have been assigned this role yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Role Statistics -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Statistics</h6>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-primary me-3">
                                <span class="mdi mdi-key icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $role->permissions->count() }}</h6>
                                <small class="text-muted">Total Permissions</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-success me-3">
                                <span class="mdi mdi-account-multiple icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $role->users->count() }}</h6>
                                <small class="text-muted">Assigned Users</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-warning me-3">
                                <span class="mdi mdi-calendar icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $role->created_at->diffInDays() }}</h6>
                                <small class="text-muted">Days Since Created</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-box-danger me-3">
                                <span class="mdi mdi-folder icon-item"></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $groupedPermissions->count() }}</h6>
                                <small class="text-muted">Permission Categories</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
