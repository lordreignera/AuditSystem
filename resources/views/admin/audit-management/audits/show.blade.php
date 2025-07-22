@extends('admin.admin_layout')

@section('title', 'Audit Details - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 class="mb-3 mb-md-0">Audit Details: {{ $audit->name }}</h2>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                @can('edit audits')
                    <a href="{{ route('admin.audits.edit', $audit) }}" class="btn btn-primary mt-2 mt-xl-0 me-2">
                        <i class="mdi mdi-pencil"></i> Edit Audit
                    </a>
                @endcan
                <a href="{{ route('admin.audits.index') }}" class="btn btn-secondary mt-2 mt-xl-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Audits
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-12 mb-4">
                        <h4 class="card-title">Basic Information</h4>
                        <hr>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Audit Name</label>
                        <p class="form-control-plaintext">{{ $audit->name }}</p>
                    </div>

                    @hasanyrole('Super Admin|Admin|Audit Manager')
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Review Code</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-info">{{ $audit->review_code }}</span>
                        </p>
                    </div>
                    @endhasanyrole

                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <p class="form-control-plaintext">{{ $audit->description ?: 'No description provided.' }}</p>
                    </div>

                    <!-- Location & Timing -->
                    <div class="col-md-12 mb-4 mt-4">
                        <h4 class="card-title">Location & Timing</h4>
                        <hr>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Country</label>
                        <p class="form-control-plaintext">
                            <i class="mdi mdi-map-marker text-primary"></i> {{ $audit->country->name }}
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Start Date</label>
                        <p class="form-control-plaintext">{{ $audit->start_date->format('F d, Y') }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Duration</label>
                        <p class="form-control-plaintext">
                            @if($audit->duration_value && $audit->duration_unit)
                                {{ $audit->duration_value }} {{ ucfirst($audit->duration_unit) }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">End Date</label>
                        <p class="form-control-plaintext">
                            @if($audit->end_date)
                                {{ $audit->end_date->format('F d, Y') }}
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $daysRemaining = $now->diffInDays($audit->end_date, false);
                                @endphp
                                @if($daysRemaining > 0)
                                    <br><span class="badge badge-success">{{ $daysRemaining }} days remaining</span>
                                @elseif($daysRemaining === 0)
                                    <br><span class="badge badge-warning">Due today</span>
                                @else
                                    <br><span class="badge badge-danger">{{ abs($daysRemaining) }} days overdue</span>
                                @endif
                            @else
                                <span class="text-muted">Not calculated</span>
                            @endif
                        </p>
                    </div>

                    <!-- Participants -->
                    <div class="col-md-12 mb-4 mt-4">
                        <h4 class="card-title">Participants</h4>
                        <hr>
                    </div>

                    <div class="col-md-12 mb-3">
                        @if($audit->participants && count($audit->participants) > 0)
                            <div class="row">
                                @foreach($audit->participants as $participant)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-account-check text-success me-2"></i>
                                            <span>{{ $participant }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted fst-italic">No participants assigned yet.</p>
                        @endif
                    </div>

                    <!-- Metadata -->
                    <div class="col-md-12 mb-4 mt-4">
                        <h4 class="card-title">Metadata</h4>
                        <hr>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Created</label>
                        <p class="form-control-plaintext">{{ $audit->created_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Last Updated</label>
                        <p class="form-control-plaintext">{{ $audit->updated_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @can('edit audits')
                                <a href="{{ route('admin.audits.edit', $audit) }}" class="btn btn-primary">
                                    <i class="mdi mdi-pencil"></i> Edit Audit
                                </a>
                            @endcan
                        </div>
                        @can('delete audits')
                            <form action="{{ route('admin.audits.destroy', $audit) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this audit? This action cannot be undone.')">
                                    <i class="mdi mdi-delete"></i> Delete Audit
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
