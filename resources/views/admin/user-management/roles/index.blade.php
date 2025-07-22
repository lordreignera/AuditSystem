@extends('admin.admin_layout')

@section('title', 'Role Management - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Role Management</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Manage system roles and their permissions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary mb-3 mb-md-0">
                    <i class="mdi mdi-shield-plus"></i> Create New Role
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ $roles->total() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Roles</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-primary">
                            <span class="mdi mdi-shield-account icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ \App\Models\User::whereHas('roles')->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Users with Roles</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-success">
                            <span class="mdi mdi-account-check icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Permissions</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-warning">
                            <span class="mdi mdi-key icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ $roles->sum(function($role) { return $role->permissions->count(); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Assignments</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-danger">
                            <span class="mdi mdi-link icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title">System Roles</h6>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="mdi mdi-account-multiple"></i> Manage Users
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="mdi mdi-key"></i> Manage Permissions
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-box-primary me-2" style="width: 32px; height: 32px;">
                                                <span class="mdi mdi-shield-account" style="font-size: 16px;"></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $role->name }}</h6>
                                                @if($role->name === 'Super Admin')
                                                    <small class="badge bg-danger">System Role</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $role->description ?? 'No description provided' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                                        @if($role->permissions->count() > 0)
                                            <button class="btn btn-sm btn-link p-0 ms-1" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#permissions-{{ $role->id }}" 
                                                    aria-expanded="false">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $role->users->count() }} users</span>
                                    </td>
                                    <td>{{ $role->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.roles.show', $role) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.roles.edit', $role) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            @if($role->name !== 'Super Admin')
                                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this role?')"
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled title="Cannot delete system role">
                                                    <i class="mdi mdi-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if($role->permissions->count() > 0)
                                    <tr class="collapse" id="permissions-{{ $role->id }}">
                                        <td colspan="6" class="bg-light">
                                            <div class="p-3">
                                                <h6>Permissions for {{ $role->name }}:</h6>
                                                <div class="d-flex flex-wrap">
                                                    @foreach($role->permissions as $permission)
                                                        <span class="badge bg-primary me-1 mb-1">{{ $permission->name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No roles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
