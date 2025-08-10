@extends('admin.admin_layout')

@section('title', 'Audit Management - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    @role('Auditor')
                        <h2 class="mb-3 mb-md-0">My Assigned Audits</h2>
                        <p class="text-muted">Click on the Dashboard button to start or continue your audit work</p>
                    @else
                        <h2 class="mb-3 mb-md-0">Audit Management</h2>
                    @endrole
                </div>
            </div>
            @can('create audits')
                <div class="d-flex justify-content-between align-items-end flex-wrap">
                    <a href="{{ route('admin.audits.create') }}" class="btn btn-primary mt-2 mt-xl-0 text-white" style="color: #ffffff !important;">
                        <i class="mdi mdi-plus"></i> Create New Audit
                    </a>
                </div>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                @hasanyrole('Super Admin|Admin|Audit Manager')
                                <th>Review Code</th>
                                @endhasanyrole
                                <th>Country</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($audits as $audit)
                                <tr>
                                    <td>
                                        <strong>{{ $audit->name }}</strong>
                                        @if($audit->description)
                                            <br><small class="text-muted">{{ Str::limit($audit->description, 60) }}</small>
                                        @endif
                                    </td>
                                    @hasanyrole('Super Admin|Admin|Audit Manager')
                                    <td>
                                        <span class="badge badge-info">{{ $audit->review_code }}</span>
                                    </td>
                                    @endhasanyrole
                                    <td>{{ $audit->country->name }}</td>
                                    <td>{{ $audit->start_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($audit->end_date)
                                            {{ $audit->end_date->format('M d, Y') }}
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $daysRemaining = $now->diffInDays($audit->end_date, false);
                                            @endphp
                                            @if($daysRemaining > 0)
                                                <br><small class="text-success">{{ $daysRemaining }} days remaining</small>
                                            @elseif($daysRemaining === 0)
                                                <br><small class="text-warning">Due today</small>
                                            @else
                                                <br><small class="text-danger">{{ abs($daysRemaining) }} days overdue</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Not calculated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($audit->end_date)
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $daysRemaining = $now->diffInDays($audit->end_date, false);
                                            @endphp
                                            @if($daysRemaining > 7)
                                                <span class="badge badge-success">Active</span>
                                            @elseif($daysRemaining > 0)
                                                <span class="badge badge-warning">Due Soon</span>
                                            @elseif($daysRemaining === 0)
                                                <span class="badge badge-warning">Due Today</span>
                                            @else
                                                <span class="badge badge-danger">Overdue</span>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Planned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.audits.dashboard', $audit) }}" class="btn btn-sm text-white" title="Audit Dashboard" style="background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%); border: none; box-shadow: 0 2px 8px rgba(37,99,235,0.15); display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="mdi mdi-view-dashboard" style="color: #fff; font-size: 1.2rem;"></i>
                                                <span style="font-weight: 500;">Dashboard</span>
                                            </a>
                                            @can('view audits')
                                                <a href="{{ route('admin.audits.show', $audit) }}" class="btn btn-info btn-sm">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('edit audits')
                                                <a href="{{ route('admin.audits.edit', $audit) }}" class="btn btn-warning btn-sm">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete audits')
                                                <form action="{{ route('admin.audits.destroy', $audit) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this audit?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    @hasanyrole('Super Admin|Admin|Audit Manager')
                                        <td colspan="7" class="text-center">
                                    @else
                                        <td colspan="6" class="text-center">
                                    @endhasanyrole
                                        <div class="py-4">
                                            <i class="mdi mdi-clipboard-text mdi-48px text-muted"></i>
                                            @role('Auditor')
                                                <p class="mt-2 text-muted">No audits have been assigned to you yet.</p>
                                                <p class="text-muted">Please contact your audit manager if you believe this is an error.</p>
                                            @else
                                                <p class="mt-2 text-muted">No audits found.</p>
                                                @can('create audits')
                                                    <a href="{{ route('admin.audits.create') }}" class="btn btn-primary text-white">
                                                        Create Your First Audit
                                                    </a>
                                                @endcan
                                            @endrole
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $audits->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
