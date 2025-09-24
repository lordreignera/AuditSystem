@extends('admin.admin_layout')

@section('title', 'Reports - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Audit Reports</h4>
                    <div>
                        <i class="mdi mdi-robot me-2"></i>
                        <span class="text-muted">Powered by AI Report Generation</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">AI-Powered Report Generation</h6>
                                    <p class="mb-0">Generate comprehensive audit reports using DeepSeek AI. Our system analyzes responses across all review types, locations, and question types to provide detailed insights and recommendations.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($audits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Audit Name</th>
                                    <th>Country</th>
                                    <th>Review Types</th>
                                    <th>Date Range</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($audits as $audit)
                                    @php
                                        $reviewTypes = $audit->attachedReviewTypes->groupBy('review_type_id');
                                        $totalLocations = $audit->attachedReviewTypes->count();
                                        $totalResponses = $audit->responses()->count();
                                        
                                        // Calculate estimated total questions
                                        $estimatedQuestions = 0;
                                        foreach($reviewTypes as $attachments) {
                                            $reviewType = $attachments->first()->reviewType;
                                            $questionsPerLocation = $reviewType->templates->sum(function($template) {
                                                return $template->sections->sum(function($section) {
                                                    return $section->questions->count();
                                                });
                                            });
                                            $estimatedQuestions += $questionsPerLocation * $attachments->count();
                                        }
                                        
                                        $completionPercentage = $estimatedQuestions > 0 ? round(($totalResponses / $estimatedQuestions) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $audit->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $audit->review_code }}</small>
                                        </td>
                                        <td>{{ $audit->country->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $reviewTypes->count() }} Types</span>
                                            <span class="badge bg-info">{{ $totalLocations }} Locations</span>
                                        </td>
                                        <td>
                                            {{ $audit->start_date->format('M j, Y') }}
                                            @if($audit->end_date)
                                                <br>
                                                <small class="text-muted">to {{ $audit->end_date->format('M j, Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $completionPercentage }}%;" 
                                                     aria-valuenow="{{ $completionPercentage }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $completionPercentage }}% Complete ({{ $totalResponses }} responses)</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.reports.show', $audit) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="mdi mdi-chart-line"></i> Generate Report
                                                </a>
                                                <a href="{{ route('admin.ai-chat.show', $audit) }}" 
                                                   class="btn btn-outline-success btn-sm">
                                                    <i class="mdi mdi-robot"></i> AI Chat
                                                </a>
                                                <a href="{{ route('admin.audits.dashboard', $audit) }}" 
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="mdi mdi-view-dashboard"></i> View Audit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-file-chart mdi-48px text-muted"></i>
                        <h5 class="mt-3 text-muted">No Audits Available</h5>
                        <p class="text-muted">Create an audit first to generate reports.</p>
                        <a href="{{ route('admin.audits.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus me-1"></i>Create New Audit
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="mdi mdi-file-multiple mdi-36px text-primary"></i>
                <h4 class="mt-2">{{ $audits->count() }}</h4>
                <p class="text-muted">Total Audits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="mdi mdi-check-circle-outline mdi-36px text-success"></i>
                <h4 class="mt-2">{{ $audits->where('end_date', '<=', now())->count() }}</h4>
                <p class="text-muted">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="mdi mdi-clock-outline mdi-36px text-warning"></i>
                <h4 class="mt-2">{{ $audits->where('end_date', '>', now())->count() }}</h4>
                <p class="text-muted">In Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="mdi mdi-robot mdi-36px text-info"></i>
                <h4 class="mt-2">AI</h4>
                <p class="text-muted">Powered</p>
            </div>
        </div>
    </div>
</div>
@endsection
