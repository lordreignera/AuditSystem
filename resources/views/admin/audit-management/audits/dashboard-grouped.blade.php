@extends('layouts.admin')

@section('title', 'Audit Dashboard - ' . $audit->title)
@section('page-title', 'Audit Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Audit Information Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-clipboard-list-outline me-2"></i>
                        {{ $audit->title }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Audit ID:</strong> {{ $audit->id }}</p>
                            <p><strong>Country:</strong> {{ $audit->country->name }}</p>
                            <p><strong>Created by:</strong> {{ $audit->creator->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $audit->start_date ? $audit->start_date->format('M d, Y') : 'Not set' }}</p>
                            <p><strong>End Date:</strong> {{ $audit->end_date ? $audit->end_date->format('M d, Y') : 'Not set' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $audit->status === 'active' ? 'success' : ($audit->status === 'completed' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($audit->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @if($audit->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="text-muted">{{ $audit->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.audits.edit', $audit) }}" class="btn btn-outline-primary btn-sm mb-2 w-100">
                        <i class="mdi mdi-pencil me-1"></i>Edit Audit Details
                    </a>
                    <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="mdi mdi-arrow-left me-1"></i>Back to Audits
                    </a>
                </div>
            </div>
        </div>
    </div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Review Types Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Review Types & Templates</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewTypeModal">
                    <i class="mdi mdi-plus me-1"></i>Attach Review Type
                </button>
            </div>
            <div class="card-body">
                @if($attachedReviewTypes && $attachedReviewTypes->count() > 0)
                    <div class="accordion" id="reviewTypesAccordion">
                        @foreach($attachedReviewTypes as $index => $reviewType)
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="heading{{ $index }}">
                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div class="d-flex align-items-center">
                                                <strong>{{ $reviewType->name }}</strong>
                                                
                                                <!-- Location badges showing master and all duplicates -->
                                                <div class="ms-3">
                                                    <!-- Master badge -->
                                                    <span class="badge bg-primary me-2">{{ $reviewType->locationName }}</span>
                                                    
                                                    <!-- Duplicate badges -->
                                                    @foreach($reviewType->duplicates as $duplicate)
                                                        <span class="badge bg-secondary me-2">{{ $duplicate->locationName }}</span>
                                                    @endforeach
                                                    
                                                    <!-- Template count -->
                                                    <span class="badge bg-info">{{ $reviewType->auditTemplates->count() }} Templates</span>
                                                </div>
                                                
                                                <!-- Facility management buttons -->
                                                <div class="ms-3">
                                                    <button class="btn btn-sm btn-success" onclick="duplicateReviewType({{ $reviewType->id }}, {{ $reviewType->attachmentId }})">
                                                        <i class="fas fa-copy"></i> Duplicate
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="renameLocation({{ $reviewType->attachmentId }}, '{{ $reviewType->locationName }}')">
                                                        <i class="fas fa-edit"></i> Rename
                                                    </button>
                                                    @if($reviewType->duplicates->count() > 0)
                                                        <button class="btn btn-sm btn-danger" onclick="detachReviewType({{ $reviewType->id }}, {{ $reviewType->attachmentId }})">
                                                            <i class="fas fa-unlink"></i> Detach All
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <span class="badge bg-info">{{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->count()) }} Sections</span>
                                                <span class="badge bg-success">{{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->sum(fn($section) => $section->questions->count())) }} Questions</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#reviewTypesAccordion">
                                    <div class="accordion-body">
                                        <!-- Location selector -->
                                        <div class="mb-3">
                                            <label for="locationSelector{{ $reviewType->id }}" class="form-label fw-bold">
                                                <i class="fas fa-map-marker-alt"></i> Select Location:
                                            </label>
                                            <select class="form-select" id="locationSelector{{ $reviewType->id }}" 
                                                    onchange="switchLocation({{ $reviewType->id }}, this.value)">
                                                <!-- Master option -->
                                                <option value="{{ $reviewType->attachmentId }}" selected>
                                                    {{ $reviewType->locationName }} (Master)
                                                </option>
                                                
                                                <!-- Duplicate options -->
                                                @foreach($reviewType->duplicates as $duplicate)
                                                    <option value="{{ $duplicate->attachmentId }}">
                                                        {{ $duplicate->locationName }} (Duplicate #{{ $duplicate->duplicateNumber }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Location-specific action buttons -->
                                        <div class="mb-3" id="locationActions{{ $reviewType->id }}">
                                            <!-- Master location actions (shown by default) -->
                                            <div class="location-actions" data-attachment-id="{{ $reviewType->attachmentId }}">
                                                <button class="btn btn-sm btn-warning me-2" 
                                                        onclick="renameLocation({{ $reviewType->attachmentId }}, '{{ $reviewType->locationName }}')">
                                                    <i class="fas fa-edit"></i> Rename Master
                                                </button>
                                                <button class="btn btn-sm btn-success me-2" 
                                                        onclick="addQuestion({{ $reviewType->id }}, {{ $reviewType->attachmentId }})">
                                                    <i class="fas fa-plus"></i> Add Question
                                                </button>
                                            </div>
                                            
                                            <!-- Duplicate location actions (hidden by default) -->
                                            @foreach($reviewType->duplicates as $duplicate)
                                                <div class="location-actions d-none" data-attachment-id="{{ $duplicate->attachmentId }}">
                                                    <button class="btn btn-sm btn-warning me-2" 
                                                            onclick="renameLocation({{ $duplicate->attachmentId }}, '{{ $duplicate->locationName }}')">
                                                        <i class="fas fa-edit"></i> Rename Duplicate
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="removeDuplicate({{ $duplicate->attachmentId }}, '{{ $duplicate->locationName }}')">
                                                        <i class="fas fa-trash"></i> Remove Duplicate
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Template sections container -->
                                        <div id="sectionsContainer{{ $reviewType->id }}">
                                            <!-- This will be populated via AJAX when location is selected -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="mdi mdi-clipboard-text mdi-48px text-muted"></i>
                        <p class="mt-2 text-muted">No review types attached to this audit yet.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewTypeModal">
                            <i class="mdi mdi-plus me-1"></i>Attach First Review Type
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- modals -->
@include('admin.audit-management.audits.modals')

<script>
// Global variables for location switching
window.currentLocations = @json($attachedReviewTypes->mapWithKeys(function($reviewType) {
    return [$reviewType->id => $reviewType->attachmentId];
}));

// Function to switch location and load content via AJAX
function switchLocation(reviewTypeId, attachmentId) {
    // Update current location
    window.currentLocations[reviewTypeId] = attachmentId;
    
    // Show/hide appropriate action buttons
    const actionsContainer = document.getElementById(`locationActions${reviewTypeId}`);
    const allActions = actionsContainer.querySelectorAll('.location-actions');
    
    allActions.forEach(action => {
        if (action.dataset.attachmentId === attachmentId) {
            action.classList.remove('d-none');
        } else {
            action.classList.add('d-none');
        }
    });
    
    // Load sections content via AJAX
    loadSectionsContent(reviewTypeId, attachmentId);
}

// Function to load sections content via AJAX
function loadSectionsContent(reviewTypeId, attachmentId) {
    const container = document.getElementById(`sectionsContainer${reviewTypeId}`);
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    fetch(`{{ route('admin.audits.load-sections', $audit) }}?review_type_id=${reviewTypeId}&attachment_id=${attachmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.html;
            } else {
                container.innerHTML = '<div class="alert alert-danger">Error loading content: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
        });
}

// Initialize page - load master sections for each review type
document.addEventListener('DOMContentLoaded', function() {
    @foreach($attachedReviewTypes as $reviewType)
        loadSectionsContent({{ $reviewType->id }}, {{ $reviewType->attachmentId }});
    @endforeach
});
</script>

<!-- scripts -->
@include('admin.audit-management.audits.scripts')

@endsection
