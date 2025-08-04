@extends('admin.admin_layout')

@section('title', 'Create Audit - ' . $template->name)

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Create New Audit</h2>
                    <p class="mb-md-0">
                        <span class="font-weight-medium">Review Type:</span> {{ $reviewType->name }} | 
                        <span class="font-weight-medium">Template:</span> {{ $template->name }}
                    </p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.review-types-crud.index') }}" 
                   class="btn btn-outline-primary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left mr-1"></i>
                    Back to Review Types
                </a>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.review-types-crud.store-audit', [$reviewType, $template]) }}">
    @csrf
    
    <!-- Basic Audit Information -->
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Audit Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="review_code">Review Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('review_code') is-invalid @enderror" 
                                       id="review_code" name="review_code" value="{{ old('review_code') }}" required>
                                @error('review_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date (Optional)</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lead_auditor">Lead Auditor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lead_auditor') is-invalid @enderror" 
                                       id="lead_auditor" name="lead_auditor" value="{{ old('lead_auditor') }}" required>
                                @error('lead_auditor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="team_members">Team Members (Optional)</label>
                                <textarea class="form-control @error('team_members') is-invalid @enderror" 
                                          id="team_members" name="team_members" rows="3">{{ old('team_members') }}</textarea>
                                @error('team_members')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Questions by Section -->
    @foreach($template->sections as $section)
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <!-- Section Header -->
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-light mr-3">{{ $section->order }}</span>
                            <div>
                                <h4 class="card-title mb-0 text-white">{{ $section->name }}</h4>
                                @if($section->description)
                                    <p class="mb-0 text-white-50">{{ $section->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="card-body">
                        @if($section->questions->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <em>No questions in this section</em>
                            </div>
                        @else
                            @foreach($section->questions as $question)
                                <div class="border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-shrink-0 mr-3">
                                                    <span class="badge badge-primary">Q{{ $question->order }}</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <!-- Question Text -->
                                                    <label class="form-label font-weight-bold">
                                                        {{ $question->question_text }}
                                                        @if($question->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="badge badge-info mr-2">{{ ucfirst($question->response_type) }}</span>
                                                        @if($question->options)
                                                            <small class="text-muted">Options: {{ implode(', ', $question->options) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Response Input based on question type -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                                                                            @switch($question->response_type)
                                                            @case('yes_no')
                                                                <select name="responses[{{ $question->id }}]" 
                                                                        class="form-control">
                                                                    <option value="">Select...</option>
                                                                    @if($question->options && is_array($question->options) && count($question->options) >= 2)
                                                                        <option value="{{ $question->options[0] }}" {{ old('responses.'.$question->id) == $question->options[0] ? 'selected' : '' }}>{{ $question->options[0] }}</option>
                                                                        <option value="{{ $question->options[1] }}" {{ old('responses.'.$question->id) == $question->options[1] ? 'selected' : '' }}>{{ $question->options[1] }}</option>
                                                                    @else
                                                                        <option value="Yes" {{ old('responses.'.$question->id) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                        <option value="No" {{ old('responses.'.$question->id) == 'No' ? 'selected' : '' }}>No</option>
                                                                    @endif
                                                                </select>
                                                                @break

                                                            @case('date')
                                                                <input type="date" name="responses[{{ $question->id }}]" 
                                                                       value="{{ old('responses.'.$question->id) }}"
                                                                       class="form-control">
                                                                @break

                                                            @case('number')
                                                                <input type="number" name="responses[{{ $question->id }}]" 
                                                                       value="{{ old('responses.'.$question->id) }}"
                                                                       class="form-control">
                                                                @break

                                                            @case('select')
                                                                @if($question->options)
                                                                    <select name="responses[{{ $question->id }}]" 
                                                                            class="form-control">
                                                                        <option value="">Select...</option>
                                                                        @foreach($question->options as $option)
                                                                            <option value="{{ $option }}" {{ old('responses.'.$question->id) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <input type="text" name="responses[{{ $question->id }}]" 
                                                                           value="{{ old('responses.'.$question->id) }}"
                                                                           class="form-control">
                                                                @endif
                                                                @break                                            @case('textarea')
                                                <textarea name="responses[{{ $question->id }}]" rows="4"
                                                          class="form-control"
                                                          placeholder="Enter your response...">{{ old('responses.'.$question->id) }}</textarea>
                                                @break

                                            @case('table')
                                                <div class="d-flex align-items-center mb-2">
                                                    <button type="button" class="btn btn-outline-info btn-sm me-2" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#tableModal{{ $question->id }}">
                                                        <i class="mdi mdi-table"></i> View Table Structure
                                                    </button>
                                                    <span class="text-muted">Use the table structure as reference</span>
                                                </div>
                                                <textarea name="responses[{{ $question->id }}]" rows="6"
                                                          class="form-control"
                                                          placeholder="Enter your response based on the table structure...">{{ old('responses.'.$question->id) }}</textarea>
                                                @break

                                                            @default
                                                                <input type="text" name="responses[{{ $question->id }}]" 
                                                                       value="{{ old('responses.'.$question->id) }}"
                                                                       class="form-control"
                                                                       placeholder="Enter your response...">
                                                        @endswitch
                                                    </div>
                                                </div>

                                                <!-- Audit Note -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Audit Note (Optional)</label>
                                                        <textarea name="audit_notes[{{ $question->id }}]" rows="3"
                                                                  class="form-control"
                                                                  placeholder="Add any additional notes or observations...">{{ old('audit_notes.'.$question->id) }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Submit Button -->
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0"><strong>Total Sections:</strong> {{ $template->sections->count() }}</p>
                            <p class="mb-0"><strong>Total Questions:</strong> {{ $template->sections->sum(function($s) { return $s->questions->count(); }) }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.review-types-crud.index') }}" 
                               class="btn btn-outline-secondary mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-check mr-1"></i>
                                Create Audit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Table Structure Modals -->
@foreach($template->sections as $section)
    @if($section->questions)
        @foreach($section->questions as $question)
            @if($question->response_type === 'table')
                <!-- Modal for Question {{ $question->id }} -->
                <div class="modal fade" id="tableModal{{ $question->id }}" tabindex="-1" 
                     aria-labelledby="tableModalLabel{{ $question->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content bg-white">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title text-dark" id="tableModalLabel{{ $question->id }}">
                                    Table Structure: {{ $question->question_text }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body bg-white">
                                <div class="table-responsive">
                                    @php
                                        $tableData = $question->parseTableStructure();
                                    @endphp
                                    
                                    @if($tableData && count($tableData) > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-primary">
                                                <tr>
                                                    @foreach($tableData[0] as $header)
                                                        <th class="text-dark">{{ $header }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for($i = 1; $i < count($tableData); $i++)
                                                    <tr>
                                                        @foreach($tableData[$i] as $cell)
                                                            <td class="text-dark">{{ $cell }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="alert alert-warning">
                                            <h6 class="text-dark">Question Text:</h6>
                                            <p class="text-dark">{{ $question->question_text }}</p>
                                            <hr>
                                            <p class="text-dark">This question requires a table-based response. Please structure your answer accordingly.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endforeach

<script>
// Auto-save form data to localStorage
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const formData = JSON.parse(localStorage.getItem('audit_form_data') || '{}');
    
    // Restore form data
    Object.keys(formData).forEach(key => {
        const element = form.querySelector(`[name="${key}"]`);
        if (element) {
            element.value = formData[key];
        }
    });
    
    // Save form data on change
    form.addEventListener('change', function(e) {
        const formData = JSON.parse(localStorage.getItem('audit_form_data') || '{}');
        formData[e.target.name] = e.target.value;
        localStorage.setItem('audit_form_data', JSON.stringify(formData));
    });
    
    // Clear saved data on successful submit
    form.addEventListener('submit', function() {
        localStorage.removeItem('audit_form_data');
    });
});
</script>
@endsection
