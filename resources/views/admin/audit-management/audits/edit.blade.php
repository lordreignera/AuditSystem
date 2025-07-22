@extends('admin.admin_layout')

@section('title', 'Edit Audit - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 class="mb-3 mb-md-0">Edit Audit: {{ $audit->name }}</h2>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
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
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.audits.update', $audit) }}" method="POST" id="audit-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Audit Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $audit->name) }}" class="form-control" required>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $audit->description) }}</textarea>
                        </div>

                        <!-- Country -->
                        <div class="col-md-6 mb-3">
                            <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id" class="form-select" required>
                                <option value="">Select a country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" {{ (old('country_id', $audit->country_id) == $country->id) ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @hasanyrole('Super Admin|Admin|Audit Manager')
                        <!-- Review Code (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label for="review_code" class="form-label">Review Code</label>
                            <input type="text" value="{{ $audit->review_code }}" class="form-control" readonly>
                        </div>
                        @endhasanyrole

                        <!-- Start Date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $audit->start_date->format('Y-m-d')) }}" class="form-control" required>
                        </div>

                        <!-- Duration Value -->
                        <div class="col-md-3 mb-3">
                            <label for="duration_value" class="form-label">Duration</label>
                            <input type="number" name="duration_value" id="duration_value" value="{{ old('duration_value', $audit->duration_value) }}" min="1" class="form-control">
                        </div>

                        <!-- Duration Unit -->
                        <div class="col-md-3 mb-3">
                            <label for="duration_unit" class="form-label">Duration Unit</label>
                            <select name="duration_unit" id="duration_unit" class="form-select">
                                <option value="">Select unit</option>
                                <option value="days" {{ old('duration_unit', $audit->duration_unit) == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="months" {{ old('duration_unit', $audit->duration_unit) == 'months' ? 'selected' : '' }}>Months</option>
                                <option value="years" {{ old('duration_unit', $audit->duration_unit) == 'years' ? 'selected' : '' }}>Years</option>
                            </select>
                        </div>

                        <!-- Current and New End Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current End Date</label>
                            <div class="alert alert-secondary">
                                {{ $audit->end_date ? $audit->end_date->format('M d, Y') : 'Not calculated' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Calculated End Date</label>
                            <div id="calculated_end_date" class="alert alert-info">
                                Will be calculated automatically
                            </div>
                        </div>

                        <!-- Participants -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Participants</label>
                            <div id="participants-container">
                                @if(old('participants', $audit->participants))
                                    @foreach(old('participants', $audit->participants) as $index => $participant)
                                        <div class="participant-row d-flex align-items-center mb-2">
                                            <input type="text" name="participants[]" value="{{ $participant }}" class="form-control me-2" placeholder="Enter participant name">
                                            <button type="button" class="btn btn-danger btn-sm remove-participant">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="participant-row d-flex align-items-center mb-2">
                                        <input type="text" name="participants[]" placeholder="Enter participant name" class="form-control me-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-participant">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" id="add-participant" class="btn btn-success btn-sm">
                                <i class="mdi mdi-plus"></i> Add Participant
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Update Audit
                        </button>
                        <a href="{{ route('admin.audits.index') }}" class="btn btn-secondary ms-2">
                            <i class="mdi mdi-cancel"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Add participant functionality
    document.getElementById('add-participant').addEventListener('click', function() {
        const container = document.getElementById('participants-container');
        const div = document.createElement('div');
        div.className = 'participant-row d-flex align-items-center mb-2';
        div.innerHTML = `
            <input type="text" name="participants[]" placeholder="Enter participant name" class="form-control me-2">
            <button type="button" class="btn btn-danger btn-sm remove-participant">
                <i class="mdi mdi-delete"></i>
            </button>
        `;
        container.appendChild(div);
    });

    // Remove participant functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-participant') || e.target.parentElement.classList.contains('remove-participant')) {
            const button = e.target.classList.contains('remove-participant') ? e.target : e.target.parentElement;
            button.closest('.participant-row').remove();
        }
    });

    // Calculate end date
    function calculateEndDate() {
        const startDate = document.getElementById('start_date').value;
        const durationValue = document.getElementById('duration_value').value;
        const durationUnit = document.getElementById('duration_unit').value;
        const endDateDiv = document.getElementById('calculated_end_date');

        if (startDate && durationValue && durationUnit) {
            const start = new Date(startDate);
            let end;

            switch (durationUnit) {
                case 'days':
                    end = new Date(start.getTime() + (durationValue * 24 * 60 * 60 * 1000));
                    break;
                case 'months':
                    end = new Date(start);
                    end.setMonth(end.getMonth() + parseInt(durationValue));
                    break;
                case 'years':
                    end = new Date(start);
                    end.setFullYear(end.getFullYear() + parseInt(durationValue));
                    break;
            }

            endDateDiv.textContent = end.toLocaleDateString();
            endDateDiv.className = 'alert alert-success';
        } else {
            endDateDiv.textContent = 'Will be calculated automatically';
            endDateDiv.className = 'alert alert-info';
        }
    }

    // Add event listeners for end date calculation
    document.getElementById('start_date').addEventListener('change', calculateEndDate);
    document.getElementById('duration_value').addEventListener('input', calculateEndDate);
    document.getElementById('duration_unit').addEventListener('change', calculateEndDate);

    // Calculate on page load
    calculateEndDate();
</script>
@endsection
