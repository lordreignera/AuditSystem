@extends('admin.admin_layout')

@section('title', 'Country Details - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Country Details</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">View country information</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div>
                    <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-primary me-2 mb-3 mb-md-0">
                        <i class="mdi mdi-pencil"></i> Edit Country
                    </a>
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary mb-3 mb-md-0">
                        <i class="mdi mdi-arrow-left"></i> Back to Countries
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center">
                        @if($country->flag)
                            <div class="me-3" style="font-size: 3rem;">{{ $country->flag }}</div>
                        @endif
                        <div>
                            <h4 class="card-title mb-1">{{ $country->name }}</h4>
                            <p class="text-muted mb-0">Country ID: #{{ $country->id }}</p>
                        </div>
                    </div>
                    <div>
                        @if($country->is_active)
                            <span class="badge badge-success badge-pill">Active</span>
                        @else
                            <span class="badge badge-danger badge-pill">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small">Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Country Name:</td>
                                    <td class="fw-bold">{{ $country->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ISO Code (2-letter):</td>
                                    <td><span class="badge badge-info">{{ $country->iso_code }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Country Code (3-letter):</td>
                                    <td><span class="badge badge-secondary">{{ $country->code }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status:</td>
                                    <td>
                                        @if($country->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small">Additional Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Phone Code:</td>
                                    <td>
                                        @if($country->phone_code)
                                            <span class="text-primary fw-bold">{{ $country->formatted_phone_code }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Currency:</td>
                                    <td>
                                        @if($country->currency)
                                            <span class="badge badge-warning">{{ $country->currency }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Flag:</td>
                                    <td>
                                        @if($country->flag)
                                            <span style="font-size: 1.5rem;">{{ $country->flag }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small">Timestamps</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 20%;">Created:</td>
                                    <td>{{ $country->created_at->format('F d, Y \a\t H:i') }}</td>
                                    <td class="text-muted">{{ $country->created_at->diffForHumans() }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated:</td>
                                    <td>{{ $country->updated_at->format('F d, Y \a\t H:i') }}</td>
                                    <td class="text-muted">{{ $country->updated_at->diffForHumans() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-primary me-2">
                                <i class="mdi mdi-pencil"></i> Edit Country
                            </a>
                            <form action="{{ route('admin.countries.toggle-status', $country) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-{{ $country->is_active ? 'warning' : 'success' }} me-2">
                                    @if($country->is_active)
                                        <i class="mdi mdi-pause"></i> Deactivate
                                    @else
                                        <i class="mdi mdi-play"></i> Activate
                                    @endif
                                </button>
                            </form>
                        </div>
                        <div>
                            <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this country? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="mdi mdi-delete"></i> Delete Country
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="mdi mdi-chart-line"></i> Quick Stats
                </h4>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status</span>
                        @if($country->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Created</span>
                        <span>{{ $country->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Last Updated</span>
                        <span>{{ $country->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>

                @if($country->phone_code && $country->currency)
                <div class="alert alert-info">
                    <h6><i class="mdi mdi-information-outline"></i> Complete Profile</h6>
                    <p class="mb-0 small">This country has all the basic information filled out.</p>
                </div>
                @else
                <div class="alert alert-warning">
                    <h6><i class="mdi mdi-alert-outline"></i> Incomplete Profile</h6>
                    <p class="mb-0 small">Consider adding 
                        @if(!$country->phone_code) phone code @endif
                        @if(!$country->phone_code && !$country->currency) and @endif
                        @if(!$country->currency) currency @endif
                        for a complete profile.
                    </p>
                </div>
                @endif

                <div class="mt-4">
                    <h6 class="text-muted mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-pencil"></i> Edit Details
                        </a>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-view-list"></i> View All Countries
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <i class="mdi mdi-check-circle text-success me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif
@endsection
