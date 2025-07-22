@extends('admin.admin_layout')

@section('title', 'Create Role - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Create New Role</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Create a new role and assign permissions</p>
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
                
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="e.g., Health Manager, District Auditor">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Describe what this role is responsible for...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Assign Permissions</label>
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
                                                           data-category="{{ $loop->index }}">
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
                                                               {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-shield-plus"></i> Create Role
                            </button>
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
                <h6 class="card-title">Role Creation Tips</h6>
                <div class="alert alert-info">
                    <h6><i class="mdi mdi-information"></i> Best Practices</h6>
                    <ul class="mb-0">
                        <li>Use descriptive names (e.g., "District Health Manager")</li>
                        <li>Assign only necessary permissions</li>
                        <li>Consider the principle of least privilege</li>
                        <li>Document the role's purpose in description</li>
                        <li>Review permissions regularly</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <h6>Permission Categories:</h6>
                    @foreach($permissions as $category => $categoryPermissions)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary text-capitalize">{{ str_replace('_', ' ', $category) }}</span>
                            <small class="text-muted">{{ $categoryPermissions->count() }} permissions</small>
                        </div>
                    @endforeach
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
                checkbox.checked = this.checked;
            });
        });
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
