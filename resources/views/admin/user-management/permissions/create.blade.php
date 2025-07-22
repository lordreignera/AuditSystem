@extends('admin.admin_layout')

@section('title', 'Create Permission - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Create New Permission</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Define a new system permission</p>
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
                
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: #2d3748 !important;">Permission Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
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
                                    <option value="web" {{ old('guard_name', 'web') === 'web' ? 'selected' : '' }}>Web</option>
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
                                <i class="mdi mdi-key-plus"></i> Create Permission
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Permission Naming Guide</h6>
                <div class="alert alert-info">
                    <h6><i class="mdi mdi-information"></i> Naming Conventions</h6>
                    <ul class="mb-0" style="color: #2d3748 !important;">
                        <li><strong>Action + Resource:</strong> "create users", "edit reports"</li>
                        <li><strong>Be Specific:</strong> "view admin dashboard" vs "view dashboard"</li>
                        <li><strong>Use Lowercase:</strong> All permission names should be lowercase</li>
                        <li><strong>No Special Characters:</strong> Use spaces, not underscores or dashes</li>
                        <li><strong>Be Descriptive:</strong> Clear what the permission allows</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <h6 style="color: #2d3748 !important;">Common Permission Examples:</h6>
                    <div class="example-permissions">
                        <span class="badge bg-primary me-1 mb-1">create users</span>
                        <span class="badge bg-success me-1 mb-1">edit audits</span>
                        <span class="badge bg-warning me-1 mb-1">delete reports</span>
                        <span class="badge bg-info me-1 mb-1">view dashboard</span>
                        <span class="badge bg-secondary me-1 mb-1">manage roles</span>
                        <span class="badge bg-danger me-1 mb-1">export data</span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 style="color: #2d3748 !important;">Common Categories:</h6>
                    <div class="categories-list">
                        <small class="text-muted d-block">User Management: create users, edit users, delete users</small>
                        <small class="text-muted d-block">Role Management: manage roles, assign roles</small>
                        <small class="text-muted d-block">Audit Management: create audits, view audits</small>
                        <small class="text-muted d-block">Report Management: generate reports, export reports</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
