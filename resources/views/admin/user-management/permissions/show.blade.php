@extends('admin.admin_layout')

@section('title', 'Permission Details - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Permission Details</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">View permission information and role assignments</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary mb-3 mb-md-0 me-2">
                    <i class="mdi mdi-arrow-left"></i> Back to Permissions
                </a>
                @can('manage permissions')
                <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-primary mb-3 mb-md-0">
                    <i class="mdi mdi-pencil"></i> Edit Permission
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">
                    <i class="mdi mdi-key-variant"></i> Permission Information
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td style="color: #2d3748 !important; font-weight: 600; width: 30%;">Permission Name:</td>
                                <td style="color: #2d3748 !important;">
                                    <span class="badge bg-primary">{{ $permission->name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #2d3748 !important; font-weight: 600;">Guard Name:</td>
                                <td style="color: #2d3748 !important;">
                                    <span class="badge bg-secondary">{{ $permission->guard_name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #2d3748 !important; font-weight: 600;">Permission ID:</td>
                                <td style="color: #718096 !important;">#{{ $permission->id }}</td>
                            </tr>
                            <tr>
                                <td style="color: #2d3748 !important; font-weight: 600;">Created:</td>
                                <td style="color: #718096 !important;">
                                    {{ $permission->created_at->format('F d, Y') }} at {{ $permission->created_at->format('g:i A') }}
                                    <small class="text-muted">({{ $permission->created_at->diffForHumans() }})</small>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #2d3748 !important; font-weight: 600;">Last Updated:</td>
                                <td style="color: #718096 !important;">
                                    {{ $permission->updated_at->format('F d, Y') }} at {{ $permission->updated_at->format('g:i A') }}
                                    <small class="text-muted">({{ $permission->updated_at->diffForHumans() }})</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                @can('manage permissions')
                <div class="mt-3">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
                        @if($permission->roles->count() === 0)
                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" 
                              style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this permission?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="mdi mdi-delete"></i> Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
    
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">
                    <i class="mdi mdi-account-group"></i> Role Assignments
                </h6>
                
                @if($permission->roles->count() > 0)
                    <p style="color: #718096 !important;">This permission is assigned to the following roles:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="color: #2d3748 !important;">Role Name</th>
                                    <th style="color: #2d3748 !important;">Guard</th>
                                    <th style="color: #2d3748 !important;">Users Count</th>
                                    <th style="color: #2d3748 !important;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permission->roles as $role)
                                <tr>
                                    <td style="color: #2d3748 !important;">
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    </td>
                                    <td style="color: #718096 !important;">{{ $role->guard_name }}</td>
                                    <td style="color: #718096 !important;">
                                        <span class="badge bg-secondary">{{ $role->users->count() }}</span>
                                    </td>
                                    <td>
                                        @can('manage roles')
                                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="mdi mdi-information"></i>
                        <strong>{{ $permission->roles->count() }}</strong> role(s) have this permission assigned.
                        Total users with this permission: <strong>{{ $permission->roles->sum(function($role) { return $role->users->count(); }) }}</strong>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        This permission is not currently assigned to any roles.
                    </div>
                    
                    @can('manage permissions')
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        Since this permission is not assigned to any roles, it can be safely deleted if no longer needed.
                    </div>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>

@if($permission->roles->count() > 0)
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">
                    <i class="mdi mdi-account-multiple"></i> Users with This Permission
                </h6>
                
                @php
                    $usersWithPermission = collect();
                    foreach($permission->roles as $role) {
                        $usersWithPermission = $usersWithPermission->merge($role->users);
                    }
                    $usersWithPermission = $usersWithPermission->unique('id');
                @endphp
                
                @if($usersWithPermission->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="color: #2d3748 !important;">User</th>
                                    <th style="color: #2d3748 !important;">Email</th>
                                    <th style="color: #2d3748 !important;">Status</th>
                                    <th style="color: #2d3748 !important;">Roles</th>
                                    <th style="color: #2d3748 !important;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersWithPermission as $user)
                                <tr>
                                    <td style="color: #2d3748 !important;">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #718096 !important;">{{ $user->email }}</td>
                                    <td>
                                        @if($user->is_active ?? true)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($user->roles as $userRole)
                                            @if($userRole->permissions->contains('id', $permission->id))
                                                <span class="badge bg-primary me-1">{{ $userRole->name }}</span>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @can('manage users')
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        No users currently have this permission through role assignments.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection
