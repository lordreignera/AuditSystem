@extends('admin.admin_layout')

@section('title', 'Review Types & Templates Structure')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Review Types & Template Structure</h2>
                    <p class="mb-md-0">Explore audit templates, sections, and questions imported from Excel files</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <span class="badge badge-info">Total Review Types: {{ $reviewTypes->count() }}</span>
            </div>
        </div>
    </div>
</div>

@if($reviewTypes->isEmpty())
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">No review types found</h4>
                        <p class="text-muted">Get started by running the seeders to import data from Excel files.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Review Types Grid -->
    @foreach($reviewTypes as $reviewType)
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <!-- Review Type Header -->
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-white">{{ $reviewType->name }}</h4>
                                <p class="mb-0 text-white-50">{{ $reviewType->description }}</p>
                            </div>
                            <div class="text-right">
                                <small class="text-white-50">Templates: {{ $reviewType->templates->count() }}</small><br>
                                <small class="text-white-50">Total Sections: {{ $reviewType->templates->sum(function($t) { return $t->sections->count(); }) }}</small><br>
                                <small class="text-white-50">Total Questions: {{ $reviewType->templates->sum(function($t) { return $t->sections->sum(function($s) { return $s->questions->count(); }); }) }}</small>
                            </div>
                        </div>
                    </div>

                    @if($reviewType->templates->isEmpty())
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="mdi mdi-file-document-outline text-muted" style="font-size: 2rem;"></i>
                                <h5 class="mt-2 text-muted">No templates available</h5>
                                <p class="text-muted">This review type doesn't have any default templates yet.</p>
                            </div>
                        </div>
                    @else
                        <!-- Templates -->
                        <div class="card-body">
                            @foreach($reviewType->templates as $template)
                                <div class="border rounded p-3 mb-3">
                                    <!-- Template Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="mdi mdi-file-document text-success mr-2"></i>
                                                {{ $template->name }}
                                            </h5>
                                            <p class="text-muted mb-0">{{ $template->description }}</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="text-right mr-3">
                                                <small class="text-muted d-block">Sections: {{ $template->sections->count() }}</small>
                                                <small class="text-muted d-block">Questions: {{ $template->sections->sum(function($s) { return $s->questions->count(); }) }}</small>
                                            </div>
                                            <a href="{{ route('admin.review-types-crud.create-audit', [$reviewType, $template]) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-plus mr-1"></i>
                                                Create Audit
                                            </a>
                                        </div>
                                    </div>

                                    @if($template->sections->isEmpty())
                                        <div class="text-center py-3 text-muted">
                                            <em>No sections available in this template</em>
                                        </div>
                                    @else
                                        <!-- Sections -->
                                        @foreach($template->sections as $section)
                                            <div class="card mb-3">
                                                <!-- Section Header -->
                                                <div class="card-header bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <span class="badge badge-primary mr-2">{{ $section->order }}</span>
                                                                {{ $section->name }}
                                                            </h6>
                                                            @if($section->description)
                                                                <small class="text-muted">{{ $section->description }}</small>
                                                            @endif
                                                        </div>
                                                        <span class="badge badge-secondary">{{ $section->questions->count() }} questions</span>
                                                    </div>
                                                </div>

                                                @if($section->questions->isEmpty())
                                                    <div class="card-body">
                                                        <div class="text-center text-muted">
                                                            <em>No questions in this section</em>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Questions Preview (first 3) -->
                                                    <div class="card-body">
                                                        @foreach($section->questions->take(3) as $question)
                                                            <div class="d-flex align-items-start mb-3 p-2 bg-light rounded">
                                                                <div class="flex-shrink-0 mr-3">
                                                                    <span class="badge badge-outline-primary">Q{{ $question->order }}</span>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <p class="mb-1 font-weight-medium">{{ $question->question_text }}</p>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge badge-info mr-2">{{ ucfirst($question->response_type) }}</span>
                                                                        @if($question->is_required)
                                                                            <span class="badge badge-danger mr-2">Required</span>
                                                                        @endif
                                                                        @if($question->options)
                                                                            <small class="text-muted">Options: {{ implode(', ', $question->options) }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                        @if($section->questions->count() > 3)
                                                            <div class="text-center">
                                                                <span class="badge badge-light">
                                                                    ... and {{ $section->questions->count() - 3 }} more questions
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
