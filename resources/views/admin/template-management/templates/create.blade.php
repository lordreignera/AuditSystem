@extends('admin.admin_layout')

@section('title', 'Create Template - ERA Health Audit Suite')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Create New Template</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Create a new default template with sections and questions</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Templates
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
                
                <form method="POST" action="{{ route('admin.templates.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: #2d3748 !important;">Template Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="e.g., National Review Template, District Audit Template"
                                       style="background-color: white !important; color: #2d3748 !important;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Give your template a descriptive name</div>
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
                                    <option value="">Select Review Type</option>
                                    @foreach($reviewTypes as $reviewType)
                                        <option value="{{ $reviewType->id }}" {{ old('review_type_id') == $reviewType->id ? 'selected' : '' }}>
                                            {{ $reviewType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('review_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Select which review type this template belongs to</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label" style="color: #2d3748 !important;">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Describe what this template is used for..."
                                          style="background-color: white !important; color: #2d3748 !important;">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional description of the template's purpose</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active" style="color: #2d3748 !important;">
                                    Active Template
                                </label>
                                <div class="form-text">Active templates can be used to create audits</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2" style="color: #ffffff !important;">
                                <i class="mdi mdi-file-document-plus"></i> Create Template
                            </button>
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card" style="background-color: white !important;">
            <div class="card-body">
                <h6 class="card-title" style="color: #2d3748 !important;">Template Creation Guide</h6>
                <div class="alert alert-info">
                    <h6><i class="mdi mdi-information"></i> What's Next?</h6>
                    <ul class="mb-0" style="color: #2d3748 !important;">
                        <li><strong>Create Template:</strong> First create the basic template information</li>
                        <li><strong>Add Sections:</strong> Organize your questions into logical sections</li>
                        <li><strong>Add Questions:</strong> Create specific questions for each section</li>
                        <li><strong>Use Template:</strong> Once complete, the template can be used for audits</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <h6 style="color: #2d3748 !important;">Template Benefits:</h6>
                    <div class="benefits-list">
                        <small class="text-muted d-block mb-1"><i class="mdi mdi-check text-success"></i> Standardized audit structure</small>
                        <small class="text-muted d-block mb-1"><i class="mdi mdi-check text-success"></i> Consistent data collection</small>
                        <small class="text-muted d-block mb-1"><i class="mdi mdi-check text-success"></i> Reusable across multiple audits</small>
                        <small class="text-muted d-block mb-1"><i class="mdi mdi-check text-success"></i> Easy duplication and modification</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 style="color: #2d3748 !important;">Review Types Available:</h6>
                    <div class="review-types-list">
                        @foreach($reviewTypes as $reviewType)
                            <small class="text-muted d-block">
                                <i class="mdi mdi-circle-small"></i> {{ $reviewType->name }}
                                @if($reviewType->description)
                                    - {{ Str::limit($reviewType->description, 30) }}
                                @endif
                            </small>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
