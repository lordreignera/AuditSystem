@extends('admin.admin_layout')

@section('title', 'Permission Test - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Permission Test</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Test your current permissions and roles</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Your Current Roles</h6>
                @if(Auth::user()->roles->count() > 0)
                    @foreach(Auth::user()->roles as $role)
                        <span class="badge bg-primary me-2 mb-2">{{ $role->name }}</span>
                    @endforeach
                @else
                    <p class="text-muted">No roles assigned</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">User Management Permissions</h6>
                <ul class="list-unstyled">
                    <li>
                        @can('manage users')
                            <i class="mdi mdi-check text-success"></i> Manage Users
                        @else
                            <i class="mdi mdi-close text-danger"></i> Manage Users
                        @endcan
                    </li>
                    <li>
                        @can('create users')
                            <i class="mdi mdi-check text-success"></i> Create Users
                        @else
                            <i class="mdi mdi-close text-danger"></i> Create Users
                        @endcan
                    </li>
                    <li>
                        @can('manage roles')
                            <i class="mdi mdi-check text-success"></i> Manage Roles
                        @else
                            <i class="mdi mdi-close text-danger"></i> Manage Roles
                        @endcan
                    </li>
                    <li>
                        @can('manage permissions')
                            <i class="mdi mdi-check text-success"></i> Manage Permissions
                        @else
                            <i class="mdi mdi-close text-danger"></i> Manage Permissions
                        @endcan
                    </li>
                    <li>
                        @can('view admin dashboard')
                            <i class="mdi mdi-check text-success"></i> View Admin Dashboard
                        @else
                            <i class="mdi mdi-close text-danger"></i> View Admin Dashboard
                        @endcan
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">All Your Permissions</h6>
                @php
                    $allPermissions = Auth::user()->roles->flatMap->permissions->unique('id');
                @endphp
                
                @if($allPermissions->count() > 0)
                    <div class="row">
                        @foreach($allPermissions as $permission)
                            <div class="col-md-3 mb-2">
                                <span class="badge bg-info">{{ $permission->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No permissions found</p>
                @endif
                
                <div class="mt-3">
                    <p><strong>Total Permissions:</strong> {{ $allPermissions->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Quick Links (Test if they work)</h6>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        <i class="mdi mdi-account-multiple"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="mdi mdi-account-plus"></i> Create User
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-warning">
                        <i class="mdi mdi-shield-account"></i> Manage Roles
                    </a>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-info">
                        <i class="mdi mdi-key"></i> Manage Permissions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
