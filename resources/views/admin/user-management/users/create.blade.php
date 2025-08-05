@extends('admin.admin_layout')

@section('title', 'Create User - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Create New User</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Add a new user to the system and assign roles</p>
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
                
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Password must be at least 8 characters long.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Assign Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox" 
                                               value="{{ $role->name }}" id="role_{{ $role->id }}" 
                                               name="roles[]" {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
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
                    
                    <!-- Audit Assignment Section (shown only when Auditor role is selected) -->
                    <div class="mb-3" id="audit-assignment-section" style="display: none;">
                        <label class="form-label">Assign Audits <small class="text-muted">(Available for Auditor role)</small></label>
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> Select which audits this auditor should have access to. They will only see assigned audits in their dashboard.
                        </div>
                        
                        @if(isset($audits) && $audits->count() > 0)
                            <div class="row">
                                @foreach($audits as $audit)
                                    <div class="col-md-12 mb-3">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           value="{{ $audit->id }}" id="audit_{{ $audit->id }}" 
                                                           name="audits[]" {{ in_array($audit->id, old('audits', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="audit_{{ $audit->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <strong>{{ $audit->name }}</strong>
                                                                <br><small class="text-muted">{{ Str::limit($audit->description, 80) }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="badge bg-primary">{{ \Carbon\Carbon::parse($audit->start_date)->format('M d, Y') }}</small>
                                                                <br><small class="text-muted">to {{ \Carbon\Carbon::parse($audit->end_date)->format('M d, Y') }}</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> No audits available for assignment.
                            </div>
                        @endif
                        
                        @error('audits')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-account-plus"></i> Create User
                            </button>
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
                <h6 class="card-title">Quick Tips</h6>
                <div class="alert alert-info">
                    <h6><i class="mdi mdi-information"></i> User Creation Guidelines</h6>
                    <ul class="mb-0">
                        <li>Email addresses must be unique</li>
                        <li>Strong passwords are recommended</li>
                        <li>Users can have multiple roles</li>
                        <li>New users are automatically verified</li>
                        <li>Users will receive login credentials via email</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <h6>Available Roles:</h6>
                    @foreach($roles as $role)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary">{{ $role->name }}</span>
                            <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleCheckboxes = document.querySelectorAll('.role-checkbox');
    const auditSection = document.getElementById('audit-assignment-section');
    const auditCheckboxes = document.querySelectorAll('input[name="audits[]"]');
    
    function toggleAuditSection() {
        const auditorSelected = Array.from(roleCheckboxes).some(checkbox => 
            checkbox.checked && checkbox.value === 'Auditor'
        );
        
        if (auditorSelected) {
            auditSection.style.display = 'block';
        } else {
            auditSection.style.display = 'none';
            // Uncheck all audit checkboxes when hiding
            auditCheckboxes.forEach(checkbox => checkbox.checked = false);
        }
    }
    
    // Check on page load (for form validation errors)
    toggleAuditSection();
    
    // Add event listeners to role checkboxes
    roleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleAuditSection);
    });
});
</script>
@endpush

@endsection
