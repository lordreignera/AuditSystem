@extends('admin.admin_layout')

@section('title', 'Permission Management - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Permission Management</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Manage system permissions and access control</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary mb-3 mb-md-0">
                    <i class="mdi mdi-key-plus"></i> Create New Permission
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card" style="background-color: white !important;">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0" style="color: #2d3748 !important;">{{ $permissions->total() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Permissions</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-primary">
                            <span class="mdi mdi-key icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card" style="background-color: white !important;">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0" style="color: #2d3748 !important;">{{ App\Models\User::whereHas('roles')->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Users with Roles</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-success">
                            <span class="mdi mdi-folder icon-item"></span>
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
                            <h3 class="mb-0">{{ \Spatie\Permission\Models\Role::count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Roles</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-warning">
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
                            <h3 class="mb-0">{{ $permissions->sum(function($permission) { return $permission->roles->count(); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Assignments</h6>
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

<!-- Permission Categories Overview -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Recent Permissions</h6>
                <div class="row">
                    @foreach($permissions->take(6) as $permission)
                        <div class="col-md-4 mb-3">
                            <div class="card" style="border-left: 4px solid #4fd1c7; background-color: white !important;">
                                <div class="card-body p-3">
                                    <h6 style="color: #2d3748 !important;">{{ $permission->name }}</h6>
                                    <p class="text-muted mb-2">{{ $permission->roles->count() }} role(s) assigned</p>
                                    <div class="d-flex flex-wrap">
                                        @foreach($permission->roles->take(2) as $role)
                                            <span class="badge bg-info me-1 mb-1" style="font-size: 0.7rem;">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                        @if($permission->roles->count() > 2)
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                                +{{ $permission->roles->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Table -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
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
                    <h6 class="card-title" style="color: #2d3748 !important;">System Permissions</h6>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="mdi mdi-account-multiple"></i> Manage Users
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="mdi mdi-shield-account"></i> Manage Roles
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped" style="background-color: white !important;">
                        <thead>
                            <tr>
                                <th style="color: #2d3748 !important;">Permission Name</th>
                                <th style="color: #2d3748 !important;">Guard</th>
                                <th style="color: #2d3748 !important;">Assigned Roles</th>
                                <th style="color: #2d3748 !important;">Created</th>
                                <th style="color: #2d3748 !important;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-box-primary me-2" style="width: 32px; height: 32px;">
                                                <span class="mdi mdi-key" style="font-size: 16px;"></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" style="color: #2d3748 !important;">{{ $permission->name }}</h6>
                                                <small class="text-muted">{{ ucfirst(explode(' ', $permission->name)[0]) }} Action</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $permission->guard_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($permission->roles->count() > 0)
                                            <span class="badge bg-success">{{ $permission->roles->count() }} roles</span>
                                            <button class="btn btn-sm btn-link p-0 ms-1" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#roles-{{ $permission->id }}" 
                                                    aria-expanded="false">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-warning">Unassigned</span>
                                        @endif
                                    </td>
                                    <td style="color: #2d3748 !important;">{{ $permission->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.permissions.show', $permission) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            @if($permission->roles->count() === 0)
                                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this permission?')"
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned permission">
                                                    <i class="mdi mdi-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if($permission->roles->count() > 0)
                                    <tr class="collapse" id="roles-{{ $permission->id }}">
                                        <td colspan="6" class="bg-light">
                                            <div class="p-3">
                                                <h6>Roles with this permission:</h6>
                                                <div class="d-flex flex-wrap">
                                                    @foreach($permission->roles as $role)
                                                        <a href="{{ route('admin.roles.show', $role) }}" 
                                                           class="badge bg-primary me-1 mb-1 text-decoration-none">
                                                            {{ $role->name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No permissions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $permissions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
