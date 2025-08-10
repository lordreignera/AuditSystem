@extends('admin.admin_layout')

@section('title', 'Template Management - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Template Management</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Manage default templates, sections, and questions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.templates.create') }}" class="btn btn-primary mb-3 mb-md-0" style="color: #ffffff !important;">
                    <i class="mdi mdi-file-document-plus"></i> Create New Template
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
                            <h3 class="mb-0">{{ $templates->total() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Templates</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-primary">
                            <span class="mdi mdi-file-document icon-item"></span>
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
                            <h3 class="mb-0">{{ $templates->where('is_active', true)->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Active Templates</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-success">
                            <span class="mdi mdi-file-check icon-item"></span>
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
                            <h3 class="mb-0">{{ $templates->sum(function($t) { return $t->sections->count(); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Sections</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-warning">
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
                            <h3 class="mb-0">{{ $templates->sum(function($t) { return $t->sections->sum(function($s) { return $s->questions->count(); }); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Questions</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-danger">
                            <span class="mdi mdi-help-circle icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Templates Table -->
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
                    <h6 class="card-title">Default Templates</h6>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.review-types-crud.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="mdi mdi-clipboard-list"></i> Review Types
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Review Type</th>
                                <th>Structure</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-box-primary me-2" style="width: 32px; height: 32px;">
                                                <span class="mdi mdi-file-document" style="font-size: 16px;"></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $template->name }}</h6>
                                                @if($template->description)
                                                    <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $template->reviewType->name }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <span class="badge bg-primary me-1">{{ $template->sections->count() }} sections</span>
                                            <span class="badge bg-success">{{ $template->sections->sum(function($s) { return $s->questions->count(); }) }} questions</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $template->is_active ? 'success' : 'danger' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.templates.show', $template) }}" 
                                               class="btn btn-sm btn-info" title="View/Manage">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.templates.edit', $template) }}" 
                                               class="btn btn-sm btn-warning" title="Edit Template">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.templates.duplicate', $template) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary" 
                                                        title="Duplicate Template">
                                                    <i class="mdi mdi-content-copy"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this template and all its sections/questions?')"
                                                        title="Delete Template">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="py-4">
                                            <i class="mdi mdi-file-document-outline text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="mt-2">No templates found</h5>
                                            <p class="text-muted">Create your first default template to get started.</p>
                                            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary" style="color: #ffffff !important;">
                                                <i class="mdi mdi-plus"></i> Create Template
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($templates->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
