@extends('admin.admin_layout')

@section('title', 'Edit Template - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Edit Template</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Modify template information</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Template
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Template Information</h6>
                
                <form method="POST" action="{{ route('admin.templates.update', $template) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: #2d3748 !important;">Template Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $template->name) }}" required
                                       style="background-color: white !important; color: #2d3748 !important;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="review_type_id" class="form-label" style="color: #2d3748 !important;">Review Type *</label>
                                <select class="form-control @error('review_type_id') is-invalid @enderror" 
                                        id="review_type_id" name="review_type_id" required
                                        style="background-color: white !important; color: #2d3748 !important;">
                                    @foreach($reviewTypes as $reviewType)
                                        <option value="{{ $reviewType->id }}" {{ old('review_type_id', $template->review_type_id) == $reviewType->id ? 'selected' : '' }}>
                                            {{ $reviewType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('review_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label" style="color: #2d3748 !important;">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          style="background-color: white !important; color: #2d3748 !important;">{{ old('description', $template->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active" style="color: #2d3748 !important;">
                                    Active Template
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="mdi mdi-content-save"></i> Update Template
                            </button>
                            <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Template Details</h6>
                
                <div class="template-info">
                    <div class="mb-3">
                        <strong>Current Review Type:</strong><br>
                        <span class="badge bg-info">{{ $template->reviewType->name }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Structure:</strong><br>
                        <span class="badge bg-primary">{{ $template->sections->count() }} sections</span>
                        <span class="badge bg-success">{{ $template->sections->sum(function($s) { return $s->questions->count(); }) }} questions</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $template->is_active ? 'success' : 'danger' }}">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Created:</strong><br>
                        <small class="text-muted">{{ $template->created_at->format('M d, Y \a\t H:i') }}</small>
                    </div>
                    
                    @if($template->updated_at != $template->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            <small class="text-muted">{{ $template->updated_at->format('M d, Y \a\t H:i') }}</small>
                        </div>
                    @endif
                </div>
                
                <hr>
                
                <div class="alert alert-warning">
                    <h6><i class="mdi mdi-alert"></i> Note</h6>
                    <p class="mb-0">Changing the review type will not affect existing sections and questions, but may cause confusion if the content doesn't match the new review type.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
