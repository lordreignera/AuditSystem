@extends('admin.admin_layout')

@section('title', 'Country Management - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Country Management</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Manage system countries and locations</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.countries.create') }}" class="btn btn-primary mb-3 mb-md-0">
                    <i class="mdi mdi-earth-plus"></i> Add New Country
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
                            <h3 class="mb-0">{{ $countries->total() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Countries</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-primary">
                            <span class="mdi mdi-earth icon-item"></span>
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
                            <h3 class="mb-0">{{ $countries->where('is_active', true)->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Active Countries</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-success">
                            <span class="mdi mdi-check-circle icon-item"></span>
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
                            <h3 class="mb-0">{{ $countries->where('is_active', false)->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Inactive Countries</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-warning">
                            <span class="mdi mdi-pause-circle icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Countries Table -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Countries List</h4>
                    <div class="d-flex">
                        <input type="text" class="form-control form-control-sm me-2" placeholder="Search countries..." id="countrySearch">
                        <button class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Flag</th>
                                <th>Country Name</th>
                                <th>ISO Code</th>
                                <th>Country Code</th>
                                <th>Phone Code</th>
                                <th>Currency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($countries as $country)
                                <tr>
                                    <td>
                                        @if($country->flag)
                                            <span style="font-size: 1.5rem;">{{ $country->flag }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $country->name }}</h6>
                                                <small class="text-muted">ID: {{ $country->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $country->iso_code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $country->code }}</span>
                                    </td>
                                    <td>
                                        @if($country->phone_code)
                                            <span class="text-primary">{{ $country->formatted_phone_code }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($country->currency)
                                            <span class="badge badge-warning">{{ $country->currency }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($country->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.countries.show', $country) }}">
                                                        <i class="mdi mdi-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.countries.edit', $country) }}">
                                                        <i class="mdi mdi-pencil me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.countries.toggle-status', $country) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            @if($country->is_active)
                                                                <i class="mdi mdi-pause me-2"></i>Deactivate
                                                            @else
                                                                <i class="mdi mdi-play me-2"></i>Activate
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this country?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="mdi mdi-delete me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="mdi mdi-earth-off" style="font-size: 48px;"></i>
                                            <p class="mt-2">No countries found</p>
                                            <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">
                                                <i class="mdi mdi-plus"></i> Add First Country
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($countries->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $countries->firstItem() }} to {{ $countries->lastItem() }} of {{ $countries->total() }} results
                        </div>
                        {{ $countries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('countrySearch').addEventListener('keyup', function() {
        // Simple client-side search
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection
