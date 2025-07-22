@extends('admin.admin_layout')

@section('title', 'Edit Review Type - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Edit Review Type: {{ $reviewType->name }}</h2>
                    <p class="mb-md-0">Update review type information</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.review-types.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                    <i class="ti-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Edit Review Type Details</h6>
                
                <form method="POST" action="{{ route('admin.review-types.update', $reviewType) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $reviewType->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $reviewType->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="color_code" class="form-label">Color Code</label>
                        <input type="color" class="form-control @error('color_code') is-invalid @enderror" 
                               id="color_code" name="color_code" value="{{ old('color_code', $reviewType->color_code ?? '#007bff') }}">
                        @error('color_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $reviewType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Review Type</button>
                    <a href="{{ route('admin.review-types.show', $reviewType) }}" class="btn btn-info">View</a>
                    <a href="{{ route('admin.review-types.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
