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
                        <a href="{{ route('admin.ai-chat.show', $audit) }}" class="btn btn-success btn-sm me-2">
                            <i class="mdi mdi-robot me-1"></i>AI Chat Assistant
                        </a>
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

<!-- Charts and Analytics Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="mdi mdi-chart-line me-2"></i>Audit Analytics & Visualizations
                    </h5>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" onclick="generateChart('completion_rates')">
                            <i class="mdi mdi-chart-bar me-1"></i>Completion Chart
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="generateChart('review_types')">
                            <i class="mdi mdi-chart-pie me-1"></i>Review Types
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="generateChart('location_comparison')">
                            <i class="mdi mdi-chart-donut me-1"></i>Location Comparison
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Chart Container -->
                        <div id="chartContainer" style="display: none;">
                            <canvas id="auditChart" width="400" height="200"></canvas>
                            <div class="mt-3 text-center">
                                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart()">
                                    <i class="mdi mdi-download me-1"></i>Download Chart
                                </button>
                            </div>
                        </div>
                        
                        <!-- Default State -->
                        <div id="chartPlaceholder" class="text-center py-5">
                            <i class="mdi mdi-chart-line" style="font-size: 64px; color: #e0e0e0;"></i>
                            <h5 class="text-muted mt-3">Generate Charts & Analytics</h5>
                            <p class="text-muted">Click the buttons above to generate visual analytics for your audit data</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Chart Legend/Info -->
                        <div id="chartInfo" style="display: none;">
                            <h6 class="text-primary">Chart Information</h6>
                            <div id="chartDetails"></div>
                        </div>
                        
                        <!-- Key Metrics -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Key Metrics</h6>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="metric-item">
                                            <h4 class="text-primary">{{ count($reviewTypesData) }}</h4>
                                            <small class="text-muted">Review Types</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-item">
                                            <h4 class="text-success">{{ $responseStats['total_responses'] }}</h4>
                                            <small class="text-muted">Total Responses</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <div class="metric-item">
                                            <h4 class="text-info">{{ collect($reviewTypesData)->sum(function($rt) { return count($rt['locations']); }) }}</h4>
                                            <small class="text-muted">Locations</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <div class="metric-item">
                                            <h4 class="text-warning">
                                                @php
                                                    $totalQuestions = 0;
                                                    $totalResponses = $responseStats['total_responses'];
                                                    foreach($reviewTypesData as $rt) {
                                                        foreach($rt['locations'] as $loc) {
                                                            $totalQuestions += $loc['total_questions'];
                                                        }
                                                    }
                                                    $completionRate = $totalQuestions > 0 ? round(($totalResponses / $totalQuestions) * 100, 1) : 0;
                                                @endphp
                                                {{ $completionRate }}%
                                            </h4>
                                            <small class="text-muted">Completion Rate</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Collection Test Panel -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">🔍 Data Collection Test</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Test what data the AI can read from your audit responses:</p>
                <button type="button" class="btn btn-info" onclick="testDataCollection()">
                    <i class="mdi mdi-database-search me-1"></i>Test Data Collection
                </button>
                <div id="dataTestResult" class="mt-3" style="display: none;"></div>
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
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                    <div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" onclick="exportToPDF(this)">
                                <i class="mdi mdi-file-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-primary" onclick="exportToWord(this)">
                                <i class="mdi mdi-file-word me-1"></i>Export Word
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                <i class="mdi mdi-file-excel me-1"></i>Export Excel
                            </button>
                        </div>
                        <button type="button" class="btn btn-info ms-2" onclick="copyToClipboard()">
                            <i class="mdi mdi-content-copy me-1"></i>Copy
                        </button>
                    </div>
                </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let currentChart = null;
let currentReportContent = '';

const auditData = {
    reviewTypes: @json($reviewTypesData),
    responseStats: @json($responseStats),
    auditInfo: {
        name: '{{ $audit->name }}',
        country: '{{ $audit->country->name }}',
        startDate: '{{ $audit->start_date->format('M j, Y') }}',
        @if($audit->end_date)
        endDate: '{{ $audit->end_date->format('M j, Y') }}',
        @endif
        reviewCode: '{{ $audit->review_code }}'
    }
};

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
                currentReportContent = result.report_content;
                
                // Create report content with metadata
                let reportDisplay = '';
                if (result.data_summary) {
                    reportDisplay += '<div class="alert alert-info mb-3">';
                    reportDisplay += '<h6>📊 Report Analysis Summary</h6>';
                    reportDisplay += `<small>Analyzed ${result.data_summary.review_types_analyzed} review types, `;
                    reportDisplay += `${result.data_summary.total_responses_analyzed} responses, `;
                    reportDisplay += `${result.data_summary.total_questions_analyzed} questions</small>`;
                    reportDisplay += '</div>';
                }
                
                // Display the report with proper styling
                reportDisplay += '<div style="background-color: #ffffff; color: #2d3748; padding: 1rem; border-radius: 0.375rem;">' +
                    '<pre style="background-color: #ffffff !important; color: #2d3748 !important; border: none; padding: 0; margin: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif !important; white-space: pre-wrap; word-wrap: break-word;">' + 
                    result.report_content + 
                    '</pre>' +
                    '</div>';
                
                document.getElementById('reportContent').innerHTML = reportDisplay;
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

// Helper function to get selected report type
function getSelectedReportType() {
    const form = document.getElementById('reportGenerationForm');
    const formData = new FormData(form);
    return formData.get('report_type') || 'comprehensive';
}

// Chart Generation Functions
function generateChart(chartType) {
    let chartData, chartConfig;
    
    switch(chartType) {
        case 'completion_rates':
            chartData = getCompletionRatesData();
            chartConfig = createBarChartConfig(chartData, 'Completion Rates by Location');
            updateChartInfo('Shows completion percentage for each location across all review types.');
            break;
            
        case 'review_types':
            chartData = getReviewTypesData();
            chartConfig = createPieChartConfig(chartData, 'Review Types Distribution');
            updateChartInfo('Distribution of responses across different review types.');
            break;
            
        case 'location_comparison':
            chartData = getLocationComparisonData();
            chartConfig = createDoughnutChartConfig(chartData, 'Location Response Comparison');
            updateChartInfo('Comparison of total responses by location.');
            break;
    }
    
    renderChart(chartConfig);
}

function getCompletionRatesData() {
    const labels = [];
    const data = [];
    const backgroundColors = [];
    
    auditData.reviewTypes.forEach((reviewType, rtIndex) => {
        reviewType.locations.forEach((location, locIndex) => {
            labels.push(`${location.location_name} (${reviewType.review_type.name})`);
            data.push(location.completion_percentage);
            
            // Color coding based on completion rate
            if (location.completion_percentage >= 80) {
                backgroundColors.push('rgba(75, 192, 192, 0.8)'); // Green
            } else if (location.completion_percentage >= 60) {
                backgroundColors.push('rgba(255, 206, 86, 0.8)'); // Yellow
            } else {
                backgroundColors.push('rgba(255, 99, 132, 0.8)'); // Red
            }
        });
    });
    
    return { labels, data, backgroundColors };
}

function getReviewTypesData() {
    const labels = [];
    const data = [];
    const backgroundColors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    
    auditData.reviewTypes.forEach((reviewType, index) => {
        labels.push(reviewType.review_type.name);
        const totalResponses = reviewType.locations.reduce((sum, loc) => sum + loc.response_count, 0);
        data.push(totalResponses);
    });
    
    return { labels, data, backgroundColors };
}

function getLocationComparisonData() {
    const locationData = {};
    
    auditData.reviewTypes.forEach(reviewType => {
        reviewType.locations.forEach(location => {
            if (!locationData[location.location_name]) {
                locationData[location.location_name] = 0;
            }
            locationData[location.location_name] += location.response_count;
        });
    });
    
    const labels = Object.keys(locationData);
    const data = Object.values(locationData);
    const backgroundColors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    
    return { labels, data, backgroundColors };
}

function createBarChartConfig(chartData, title) {
    return {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Completion %',
                data: chartData.data,
                backgroundColor: chartData.backgroundColors,
                borderColor: chartData.backgroundColors.map(color => color.replace('0.8', '1')),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: title,
                    font: { size: 16, weight: 'bold' }
                },
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    };
}

function createPieChartConfig(chartData, title) {
    return {
        type: 'pie',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.backgroundColors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: title,
                    font: { size: 16, weight: 'bold' }
                },
                legend: {
                    position: 'right'
                }
            }
        }
    };
}

function createDoughnutChartConfig(chartData, title) {
    return {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.backgroundColors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: title,
                    font: { size: 16, weight: 'bold' }
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    };
}

function renderChart(config) {
    const ctx = document.getElementById('auditChart').getContext('2d');
    
    if (currentChart) {
        currentChart.destroy();
    }
    
    currentChart = new Chart(ctx, config);
    
    // Show chart container and hide placeholder
    document.getElementById('chartContainer').style.display = 'block';
    document.getElementById('chartPlaceholder').style.display = 'none';
    document.getElementById('chartInfo').style.display = 'block';
}

function updateChartInfo(description) {
    document.getElementById('chartDetails').innerHTML = `
        <p class="small text-muted">${description}</p>
        <div class="mt-2">
            <small><strong>Last Updated:</strong> ${new Date().toLocaleString()}</small>
        </div>
    `;
}

function downloadChart() {
    if (currentChart) {
        const url = currentChart.toBase64Image();
        const link = document.createElement('a');
        link.href = url;
        link.download = `${auditData.auditInfo.name}_chart.png`;
        link.click();
    }
}

// Export Functions
function exportToPDF(btn = null) {
    if (!currentReportContent) {
        alert('No report to export. Please generate a report first.');
        return;
    }
    
    // Use backend PDF generation for generated reports
    const formData = new FormData();
    formData.append('report_content', currentReportContent);
    formData.append('report_type', getSelectedReportType());
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Show loading
    if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Generating PDF...';
        btn.disabled = true;
        
        fetch('{{ route("admin.reports.export-pdf", $audit) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    // HTML fallback - open in new window for printing
                    return response.text();
                } else {
                    // PDF blob
                    return response.blob();
                }
            } else {
                throw new Error('Failed to generate PDF');
            }
        })
        .then(result => {
            if (typeof result === 'string') {
                // HTML content - open in new window
                const newWindow = window.open();
                newWindow.document.write(result);
                newWindow.document.close();
            } else {
                // PDF blob - download
                const url = window.URL.createObjectURL(result);
                const link = document.createElement('a');
                link.href = url;
                link.download = `Audit_Report_${auditData.auditInfo.name.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0, 10)}.pdf`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate PDF. Please try again.');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}



function exportToExcel() {
    if (!auditData.reviewTypes || auditData.reviewTypes.length === 0) {
        alert('No audit data to export.');
        return;
    }
    
    // Create workbook
    const wb = XLSX.utils.book_new();
    
    // Summary sheet
    const summaryData = [
        ['Audit Name', auditData.auditInfo.name],
        ['Country', auditData.auditInfo.country],
        ['Start Date', auditData.auditInfo.startDate],
        ['Review Code', auditData.auditInfo.reviewCode],
        ['Generated', new Date().toLocaleDateString()],
        [''],
        ['Total Review Types', auditData.reviewTypes.length],
        ['Total Responses', auditData.responseStats.total_responses]
    ];
    
    const summarySheet = XLSX.utils.aoa_to_sheet(summaryData);
    XLSX.utils.book_append_sheet(wb, summarySheet, 'Summary');
    
    // Detailed data sheet
    const detailData = [['Review Type', 'Location', 'Type', 'Responses', 'Total Questions', 'Completion %']];
    
    auditData.reviewTypes.forEach(reviewType => {
        reviewType.locations.forEach(location => {
            detailData.push([
                reviewType.review_type.name,
                location.location_name,
                location.is_master ? 'Master' : `Duplicate #${location.duplicate_number}`,
                location.response_count,
                location.total_questions,
                location.completion_percentage
            ]);
        });
    });
    
    const detailSheet = XLSX.utils.aoa_to_sheet(detailData);
    XLSX.utils.book_append_sheet(wb, detailSheet, 'Detailed Data');
    
    // Save file
    XLSX.writeFile(wb, `${auditData.auditInfo.name}_Audit_Data.xlsx`);
}

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

// Test what data the AI can read
async function testDataCollection() {
    const testBtn = event.target;
    const resultDiv = document.getElementById('dataTestResult');
    
    // Show loading
    testBtn.disabled = true;
    testBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Testing...';
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="alert alert-info">Testing data collection...</div>';
    
    try {
        const response = await fetch('{{ route("admin.reports.debug-data", $audit) }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            let html = '<div class="alert alert-success"><h6>✅ Data Collection Test Results</h6>';
            html += `<p><strong>Total Review Types:</strong> ${result.summary.total_review_types}</p>`;
            html += `<p><strong>Total Responses Found:</strong> ${result.summary.total_responses}</p>`;
            html += `<p><strong>Total Questions:</strong> ${result.summary.total_questions}</p>`;
            
            if (result.summary.total_responses > 0) {
                html += '<h6 class="mt-3">📋 Preview of Available Data:</h6>';
                result.review_types_preview.forEach((rt, index) => {
                    html += `<div class="mb-2 p-2 border rounded">`;
                    html += `<strong>${rt.name}</strong> (${rt.locations_count} locations, ${rt.templates_count} templates)<br>`;
                    if (rt.templates_preview && rt.templates_preview.length > 0) {
                        html += `<small class="text-info">Templates: ${rt.templates_preview.join(', ')}${rt.templates_count > 3 ? '...' : ''}</small><br>`;
                    }
                    if (rt.first_location_preview !== 'No locations') {
                        html += `<small class="text-muted">Sample: ${rt.first_location_preview.name} - ${rt.first_location_preview.completion_rate} complete</small><br>`;
                        if (rt.first_location_preview.first_section_preview !== 'No sections') {
                            html += `<small class="text-info">Section: ${rt.first_location_preview.first_section_preview.name} (${rt.first_location_preview.first_section_preview.questions_count} questions)</small><br>`;
                            html += `<small class="text-secondary">Template: ${rt.first_location_preview.first_section_preview.template_name}</small><br>`;
                            html += `<small class="text-secondary">Sample Q: ${rt.first_location_preview.first_section_preview.sample_question}</small>`;
                        }
                    }
                    html += `</div>`;
                });
                html += '<p class="mt-2 text-success"><strong>✅ AI can read this data and generate comprehensive reports!</strong></p>';
            } else {
                html += '<p class="text-warning">⚠️ No responses found. Please ensure audit data has been entered.</p>';
            }
            html += '</div>';
            
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">❌ Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Test failed:', error);
        resultDiv.innerHTML = `<div class="alert alert-danger">❌ Test failed: ${error.message}</div>`;
    } finally {
        testBtn.disabled = false;
        testBtn.innerHTML = '<i class="mdi mdi-database-search me-1"></i>Test Data Collection';
    }
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
