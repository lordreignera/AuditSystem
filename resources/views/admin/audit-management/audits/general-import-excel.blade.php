@extends('layouts.admin')

@section('title', 'Import Excel - ' . $audit->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Import Excel Data - {{ $audit->name }}</h5>
                    <a href="{{ route('admin.audits.dashboard', $audit->id) }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i>Back to Audit
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($importDisabled) && $importDisabled)
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert me-2"></i>
                            <strong>Import Temporarily Unavailable</strong><br>
                            {{ $importMessage ?? 'Excel import functionality is currently being updated. Please use CSV export for data extraction.' }}
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-primary mb-3">Select Review Type to Import</h6>
                            <p class="text-muted mb-4">Choose which review type you want to import Excel data for. You can import data to create new attachments or update existing ones.</p>
                            
                            @if($attachedReviewTypes && $attachedReviewTypes->count() > 0)
                                <div class="row">
                                    @foreach($attachedReviewTypes as $reviewType)
                                        <div class="col-md-6 mb-4">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h6 class="card-title text-dark">{{ $reviewType->name }}</h6>
                                                    <p class="card-text text-muted small mb-3">
                                                        {{ $reviewType->templates->count() }} Template(s) • 
                                                        {{ $reviewType->attachments()->where('audit_id', $audit->id)->count() }} Attachment(s)
                                                    </p>
                                                    
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <a href="{{ route('admin.audits.show-import-form', [$audit->id, $reviewType->id]) }}" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                                        </a>
                                                        <a href="{{ route('admin.audits.download-blank-template', [$audit->id, $reviewType->id]) }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="mdi mdi-download me-1"></i>Download Template
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert me-2"></i>
                                    <strong>No Review Types Found</strong><br>
                                    You need to attach review types to this audit before you can import Excel data.
                                    <a href="{{ route('admin.audits.dashboard', $audit->id) }}" class="alert-link">Go back to attach review types</a>.
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-information me-2"></i>Import Instructions
                                    </h6>
                                    <ul class="list-unstyled small text-muted">
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            Download the blank template first if you don't have an Excel file
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            Each template will be a separate sheet in the Excel file
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            You can create new attachments or update existing ones
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            The system will match questions by template, section, and question text
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            You can preview the import before processing
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
