@extends('admin.admin_layout')

@section('title', 'Edit Role - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Edit Role: {{ $role->name }}</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Update role information and permissions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Roles
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Information</h6>
                
                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $role->name) }}" required
                                       {{ $role->name === 'Super Admin' ? 'readonly' : '' }}>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($role->name === 'Super Admin')
                                    <div class="form-text text-warning">System role name cannot be changed.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Describe what this role is responsible for...">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Assign Permissions</label>
                        @if($role->name === 'Super Admin')
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> Super Admin automatically has all permissions and cannot be modified.
                            </div>
                        @endif
                        <div class="card">
                            <div class="card-body">
                                @foreach($permissions as $category => $categoryPermissions)
                                    <div class="permission-category mb-4">
                                        <h6 class="text-capitalize text-primary">
                                            <i class="mdi mdi-folder"></i> {{ str_replace('_', ' ', $category) }} Permissions
                                        </h6>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input category-toggle" 
                                                           type="checkbox" 
                                                           id="category_{{ $loop->index }}"
                                                           data-category="{{ $loop->index }}"
                                                           {{ $role->name === 'Super Admin' ? 'disabled checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="category_{{ $loop->index }}">
                                                        Select All {{ str_replace('_', ' ', $category) }}
                                                    </label>
                                                </div>
                                            </div>
                                            @foreach($categoryPermissions as $permission)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               value="{{ $permission->name }}" 
                                                               id="permission_{{ $permission->id }}" 
                                                               name="permissions[]" 
                                                               data-category="{{ $loop->parent->index }}"
                                                               {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                               {{ $role->name === 'Super Admin' ? 'disabled' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <strong>{{ ucfirst(explode(' ', $permission->name)[0]) }}</strong>
                                                            {{ ucwords(str_replace($permission->name[0], '', $permission->name)) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if(!$loop->last)
                                            <hr>
                                        @endif
                                    </div>
                                @endforeach
                                @error('permissions')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2" {{ $role->name === 'Super Admin' ? 'disabled' : '' }}>
                                <i class="mdi mdi-content-save"></i> Update Role
                            </button>
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info me-2">
                                <i class="mdi mdi-eye"></i> View Role
                            </a>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Details</h6>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="icon icon-box-primary me-3" style="width: 64px; height: 64px;">
                        <span class="mdi mdi-shield-account" style="font-size: 32px;"></span>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $role->name }}</h6>
                        @if($role->name === 'Super Admin')
                            <small class="badge bg-danger">System Role</small>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Permissions:</small>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                        </p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Users:</small>
                        <p class="mb-0">
                            <span class="badge bg-success">{{ $role->users->count() }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Created:</small>
                        <p class="mb-0">{{ $role->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Updated:</small>
                        <p class="mb-0">{{ $role->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
                
                @if($role->name === 'Super Admin')
                    <div class="alert alert-info">
                        <small><i class="mdi mdi-information"></i> System role with full access to all features.</small>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <small><i class="mdi mdi-alert"></i> Changes will affect {{ $role->users->count() }} user(s).</small>
                    </div>
                @endif
                
                <div class="mt-3">
                    <h6>Current Permissions:</h6>
                    <div class="permission-preview" style="max-height: 200px; overflow-y: auto;">
                        @foreach($role->permissions->take(10) as $permission)
                            <span class="badge bg-primary me-1 mb-1">{{ $permission->name }}</span>
                        @endforeach
                        @if($role->permissions->count() > 10)
                            <span class="text-muted">... and {{ $role->permissions->count() - 10 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle category toggle functionality
    const categoryToggles = document.querySelectorAll('.category-toggle');
    
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const category = this.dataset.category;
            const checkboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
            
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = this.checked;
                }
            });
        });
        
        // Initialize category toggle state
        const category = toggle.dataset.category;
        const categoryCheckboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
        const checkedCount = Array.from(categoryCheckboxes).filter(cb => cb.checked).length;
        const totalCount = categoryCheckboxes.length;
        
        if (checkedCount === totalCount) {
            toggle.checked = true;
        } else if (checkedCount > 0) {
            toggle.indeterminate = true;
        }
    });
    
    // Update category toggle when individual permissions change
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const category = this.dataset.category;
            const categoryCheckboxes = document.querySelectorAll(`input[data-category="${category}"].permission-checkbox`);
            const categoryToggle = document.querySelector(`input[data-category="${category}"].category-toggle`);
            
            const checkedCount = Array.from(categoryCheckboxes).filter(cb => cb.checked).length;
            const totalCount = categoryCheckboxes.length;
            
            if (checkedCount === 0) {
                categoryToggle.checked = false;
                categoryToggle.indeterminate = false;
            } else if (checkedCount === totalCount) {
                categoryToggle.checked = true;
                categoryToggle.indeterminate = false;
            } else {
                categoryToggle.checked = false;
                categoryToggle.indeterminate = true;
            }
        });
    });
});
</script>
@endsection
