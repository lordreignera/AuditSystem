@extends('admin.admin_layout')

@section('title', 'Edit Permission - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Edit Permission</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Modify existing permission details</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Permissions
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Permission Information</h6>
                
                <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: #2d3748 !important;">Permission Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $permission->name) }}" required
                                       placeholder="e.g., create reports, manage audits"
                                       style="background-color: white !important; color: #2d3748 !important;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Use descriptive names like "create reports" or "manage users"</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="guard_name" class="form-label" style="color: #2d3748 !important;">Guard Name *</label>
                                <select class="form-control @error('guard_name') is-invalid @enderror" 
                                        id="guard_name" name="guard_name" required
                                        style="background-color: white !important; color: #2d3748 !important;">
                                    <option value="web" {{ old('guard_name', $permission->guard_name) === 'web' ? 'selected' : '' }}>Web</option>
                                </select>
                                @error('guard_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The guard that this permission applies to</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-content-save"></i> Update Permission
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                            <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-info">
                                <i class="mdi mdi-eye"></i> View Permission
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Permission Details</h6>
                
                <div class="mb-3">
                    <label class="form-label" style="color: #2d3748 !important;">Current Name:</label>
                    <p class="text-muted">{{ $permission->name }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="color: #2d3748 !important;">Guard Name:</label>
                    <p class="text-muted">{{ $permission->guard_name }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="color: #2d3748 !important;">Created:</label>
                    <p class="text-muted">{{ $permission->created_at->format('M d, Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="color: #2d3748 !important;">Last Updated:</label>
                    <p class="text-muted">{{ $permission->updated_at->format('M d, Y H:i') }}</p>
                </div>
                
                @if($permission->roles->count() > 0)
                <div class="mb-3">
                    <label class="form-label" style="color: #2d3748 !important;">Assigned to Roles:</label>
                    <div class="assigned-roles">
                        @foreach($permission->roles as $role)
                            <span class="badge bg-info me-1 mb-1">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div class="alert alert-warning">
                    <h6><i class="mdi mdi-alert"></i> Important Notes</h6>
                    <ul class="mb-0" style="color: #2d3748 !important;">
                        <li>Changing the permission name may affect role assignments</li>
                        <li>Ensure the new name follows naming conventions</li>
                        <li>Permission names must be unique in the system</li>
                        @if($permission->roles->count() > 0)
                            <li><strong>This permission is currently assigned to {{ $permission->roles->count() }} role(s)</strong></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
