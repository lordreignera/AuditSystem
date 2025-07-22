@extends('admin.admin_layout')

@section('title', 'Review Types - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Review Types Management</h2>
                    <p class="mb-md-0">Manage different types of health audit reviews</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.review-types.create') }}" class="btn btn-primary mb-3 mb-md-0">
                    <i class="ti-plus"></i> Add Review Type
                </a>
            </div>
        </div>
    </div>
</div>

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

                <h6 class="card-title">Review Types List</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Color</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewTypes as $reviewType)
                                <tr>
                                    <td>{{ $reviewType->id }}</td>
                                    <td>{{ $reviewType->name }}</td>
                                    <td>{{ Str::limit($reviewType->description, 50) ?? 'No description' }}</td>
                                    <td>
                                        @if($reviewType->color_code)
                                            <span class="badge" style="background-color: {{ $reviewType->color_code }};">
                                                {{ $reviewType->color_code }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No color</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $reviewType->is_active ? 'success' : 'danger' }}">
                                            {{ $reviewType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.review-types.show', $reviewType) }}" 
                                               class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('admin.review-types.edit', $reviewType) }}" 
                                               class="btn btn-sm btn-warning">Edit</a>
                                            <form method="POST" action="{{ route('admin.review-types.destroy', $reviewType) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this review type?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No review types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
