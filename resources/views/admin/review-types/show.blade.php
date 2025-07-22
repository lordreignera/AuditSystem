@extends('admin.admin_layout')

@section('title', 'View Review Type - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Review Type: {{ $reviewType->name }}</h2>
                    <p class="mb-md-0">Review type details and information</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div class="btn-group">
                    <a href="{{ route('admin.review-types.index') }}" class="btn btn-secondary mb-3 mb-md-0">
                        <i class="ti-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.review-types.edit', $reviewType) }}" class="btn btn-warning mb-3 mb-md-0">
                        <i class="ti-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Review Type Information</h6>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>ID:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $reviewType->id }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Name:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $reviewType->name }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Description:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $reviewType->description ?? 'No description provided' }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Color Code:</strong>
                    </div>
                    <div class="col-sm-9">
                        @if($reviewType->color_code)
                            <span class="badge me-2" style="background-color: {{ $reviewType->color_code }};">
                                {{ $reviewType->color_code }}
                            </span>
                            <small class="text-muted">{{ $reviewType->color_code }}</small>
                        @else
                            <span class="text-muted">No color assigned</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-{{ $reviewType->is_active ? 'success' : 'danger' }}">
                            {{ $reviewType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $reviewType->created_at ? $reviewType->created_at->format('M d, Y \a\t g:i A') : 'Not available' }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $reviewType->updated_at ? $reviewType->updated_at->format('M d, Y \a\t g:i A') : 'Not available' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
