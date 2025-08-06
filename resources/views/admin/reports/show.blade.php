@extends('admin.admin_layout')

@section('title', 'Generate Report - ' . $audit->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $audit->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Audit Information Header -->
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-white">{{ $audit->name }}</h3>
                        <p class="mb-0 text-white-50">
                            <i class="mdi mdi-map-marker me-1"></i>{{ $audit->country->name }} | 
                            <i class="mdi mdi-calendar me-1"></i>{{ $audit->start_date->format('M j, Y') }}
                            @if($audit->end_date)
                                - {{ $audit->end_date->format('M j, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-light">{{ $audit->review_code }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $audit->description ?? 'No description provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <h6>Statistics</h6>
                        <ul class="list-unstyled">
                            <li><strong>Total Responses:</strong> {{ $responseStats['total_responses'] }}</li>
                            <li><strong>Review Types:</strong> {{ count($reviewTypesData) }}</li>
                            <li><strong>Locations:</strong> {{ collect($reviewTypesData)->sum(function($rt) { return count($rt['locations']); }) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Report Generation Panel -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="mdi mdi-robot me-2"></i>AI Report Generation
                </h5>
            </div>
            <div class="card-body">
                <form id="reportGenerationForm">
                    <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                    
                    <!-- Report Type Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Report Type</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="executive_summary" value="executive_summary" checked>
                                    <label class="form-check-label" for="executive_summary">
                                        <strong>Executive Summary</strong>
                                        <br><small class="text-muted">High-level overview and key findings</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="detailed_analysis" value="detailed_analysis">
                                    <label class="form-check-label" for="detailed_analysis">
                                        <strong>Detailed Analysis</strong>
                                        <br><small class="text-muted">Comprehensive analysis of all responses</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="compliance_check" value="compliance_check">
                                    <label class="form-check-label" for="compliance_check">
                                        <strong>Compliance Check</strong>
                                        <br><small class="text-muted">Focus on compliance and standards</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="comparative_analysis" value="comparative_analysis">
                                    <label class="form-check-label" for="comparative_analysis">
                                        <strong>Comparative Analysis</strong>
                                        <br><small class="text-muted">Compare responses across locations</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Locations</label>
                        <small class="text-muted d-block mb-2">Choose specific locations to include in the report (leave empty to include all)</small>
                        
                        @foreach($reviewTypesData as $reviewTypeData)
                            <div class="mb-3">
                                <h6 class="text-primary">{{ $reviewTypeData['review_type']->name }}</h6>
                                <div class="row">
                                    @foreach($reviewTypeData['locations'] as $location)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input location-checkbox" type="checkbox" 
                                                       name="selected_locations[]" 
                                                       value="{{ $location['attachment_id'] }}" 
                                                       id="location_{{ $location['attachment_id'] }}">
                                                <label class="form-check-label" for="location_{{ $location['attachment_id'] }}">
                                                    <strong>{{ $location['location_name'] }}</strong>
                                                    @if($location['is_master'])
                                                        <span class="badge bg-success ms-1">Master</span>
                                                    @else
                                                        <span class="badge bg-info ms-1">Duplicate #{{ $location['duplicate_number'] }}</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $location['response_count'] }}/{{ $location['total_questions'] }} responses 
                                                        ({{ $location['completion_percentage'] }}% complete)
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllLocations()">Select All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllLocations()">Clear All</button>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Additional Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_tables" id="include_tables" value="1" checked>
                            <label class="form-check-label" for="include_tables">
                                Include Table Data Analysis
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_recommendations" id="include_recommendations" value="1" checked>
                            <label class="form-check-label" for="include_recommendations">
                                Include AI Recommendations
                            </label>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg" id="generateBtn">
                            <i class="mdi mdi-robot me-2"></i>Generate AI Report
                        </button>
                    </div>
                </form>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">AI is analyzing your audit data and generating the report...</p>
                    <small class="text-muted">This may take up to 2 minutes depending on the amount of data.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Data Overview -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Audit Data Overview</h6>
            </div>
            <div class="card-body">
                <!-- Response Statistics -->
                <div class="mb-4">
                    <h6 class="text-primary">Response Statistics</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Responses:</strong> {{ $responseStats['total_responses'] }}</li>
                        @foreach($responseStats['by_type'] as $type => $count)
                            <li><span class="text-capitalize">{{ str_replace('_', ' ', $type) }}:</span> {{ $count }}</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Review Types Summary -->
                <div class="mb-4">
                    <h6 class="text-primary">Review Types</h6>
                    @foreach($reviewTypesData as $reviewTypeData)
                        <div class="mb-2">
                            <strong>{{ $reviewTypeData['review_type']->name }}</strong>
                            <br>
                            <small class="text-muted">{{ count($reviewTypeData['locations']) }} location(s)</small>
                            @foreach($reviewTypeData['locations'] as $location)
                                <br>
                                <small class="text-muted ms-3">
                                    {{ $location['location_name'] }}: {{ $location['completion_percentage'] }}% complete
                                </small>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <!-- Location Summary -->
                @if(count($responseStats['by_location']) > 0)
                    <div>
                        <h6 class="text-primary">Responses by Location</h6>
                        @foreach($responseStats['by_location'] as $location => $count)
                            <div class="d-flex justify-content-between">
                                <span>{{ $location }}</span>
                                <span class="badge bg-info">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- API Configuration Status -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">AI Configuration</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if(config('services.deepseek.api_key'))
                        <i class="mdi mdi-check-circle text-success me-2"></i>
                        <span class="text-success">DeepSeek AI Configured</span>
                    @else
                        <i class="mdi mdi-close-circle text-danger me-2"></i>
                        <span class="text-danger">API Key Required</span>
                    @endif
                </div>
                <small class="text-muted">
                    Model: {{ config('services.deepseek.model', 'deepseek-chat') }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Report Display Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Generated Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reportContent" class="report-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadReport()">
                    <i class="mdi mdi-download me-1"></i>Download Report
                </button>
                <button type="button" class="btn btn-success" onclick="copyToClipboard()">
                    <i class="mdi mdi-content-copy me-1"></i>Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.report-content {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    max-height: 70vh;
    overflow-y: auto;
    background-color: #ffffff;
    color: #2d3748;
    padding: 1rem;
    border-radius: 0.375rem;
}

.report-content pre {
    background-color: #ffffff !important;
    color: #2d3748 !important;
    border: none;
    padding: 0;
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.report-content h1, .report-content h2, .report-content h3 {
    color: #2d3748 !important;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.report-content h1 {
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.report-content h2 {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.3rem;
}

.report-content ul {
    padding-left: 1.5rem;
}

.report-content li {
    margin-bottom: 0.5rem;
}

/* Ensure modal body has light background */
#reportModal .modal-body {
    background-color: #ffffff;
}

#reportModal .modal-content {
    background-color: #ffffff;
}

/* Style for better readability */
.report-content strong {
    color: #1a202c !important;
    font-weight: 600;
}

.report-content em {
    color: #4a5568 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reportGenerationForm');
    const generateBtn = document.getElementById('generateBtn');
    const loadingState = document.getElementById('loadingState');
    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate API key
        @if(!config('services.deepseek.api_key'))
            alert('DeepSeek API key is not configured. Please add DEEPSEEK_API_KEY to your .env file.');
            return;
        @endif

        // Show loading state
        generateBtn.style.display = 'none';
        loadingState.style.display = 'block';

        try {
            const formData = new FormData(form);
            
            const response = await fetch('{{ route("admin.reports.generate", $audit) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Display the report with proper styling
                document.getElementById('reportContent').innerHTML = 
                    '<div style="background-color: #ffffff; color: #2d3748; padding: 1rem; border-radius: 0.375rem;">' +
                    '<pre style="background-color: #ffffff !important; color: #2d3748 !important; border: none; padding: 0; margin: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif !important; white-space: pre-wrap; word-wrap: break-word;">' + 
                    result.report_content + 
                    '</pre>' +
                    '</div>';
                reportModal.show();
            } else {
                alert('Error generating report: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while generating the report. Please try again.');
        } finally {
            // Hide loading state
            generateBtn.style.display = 'inline-block';
            loadingState.style.display = 'none';
        }
    });
});

function selectAllLocations() {
    document.querySelectorAll('.location-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function clearAllLocations() {
    document.querySelectorAll('.location-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function downloadReport() {
    const content = document.getElementById('reportContent').textContent;
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '{{ $audit->name }} - AI Report.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function copyToClipboard() {
    const content = document.getElementById('reportContent').textContent;
    navigator.clipboard.writeText(content).then(() => {
        alert('Report copied to clipboard!');
    }).catch(err => {
        console.error('Error copying to clipboard:', err);
        alert('Failed to copy to clipboard. Please select and copy manually.');
    });
}
</script>
@endsection
