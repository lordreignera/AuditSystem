@extends('admin.admin_layout')

@section('title', 'Import Excel Data')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-file-excel"></i> 
                        Import Excel Data for {{ $reviewType->name }}
                    </h4>
                    <p class="text-muted mt-2">Audit: {{ $audit->name }}</p>
                </div>
                <div class="card-body">
                    
                    <!-- Download Blank Template Section -->
                    <div class="alert alert-info">
                        <h6><i class="mdi mdi-information"></i> Need a Template?</h6>
                        <p class="mb-2">Download a blank Excel template with the correct structure for this review type:</p>
                        <a href="{{ route('admin.audits.download-blank-template', [$audit->id, $reviewType->id]) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-download"></i> Download Blank Template
                        </a>
                    </div>

                    <!-- Import Form -->
                    <form id="importForm" method="POST" action="{{ route('admin.audits.import-excel', [$audit->id, $reviewType->id]) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="form-group mb-4">
                            <label for="excel_file" class="form-label">
                                <i class="mdi mdi-file-excel"></i> Select Excel File
                            </label>
                            <input type="file" 
                                   id="excel_file" 
                                   name="excel_file" 
                                   class="form-control" 
                                   accept=".xlsx,.xls" 
                                   required>
                            <small class="form-text text-muted">
                                Supported formats: .xlsx, .xls (Maximum size: 10MB)
                            </small>
                        </div>

                        <!-- Import Mode Selection -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="mdi mdi-cogs"></i> Import Mode
                            </label>
                            <div class="form-check">
                                <input type="radio" 
                                       id="import_mode_new" 
                                       name="import_mode" 
                                       value="new" 
                                       class="form-check-input" 
                                       checked>
                                <label for="import_mode_new" class="form-check-label">
                                    <strong>Create New Location</strong> - Import as a new duplicate/location
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" 
                                       id="import_mode_update" 
                                       name="import_mode" 
                                       value="update" 
                                       class="form-check-input">
                                <label for="import_mode_update" class="form-check-label">
                                    <strong>Update Existing Location</strong> - Overwrite responses for an existing location
                                </label>
                            </div>
                        </div>

                        <!-- New Location Name (shown when "new" is selected) -->
                        <div id="new_location_section" class="form-group mb-4">
                            <label for="location_name" class="form-label">
                                <i class="mdi mdi-map-marker"></i> Location Name
                            </label>
                            <input type="text" 
                                   id="location_name" 
                                   name="location_name" 
                                   class="form-control" 
                                   placeholder="e.g., Branch Hospital, Main Office">
                            <small class="form-text text-muted">
                                Enter a descriptive name for this location/duplicate
                            </small>
                        </div>

                        <!-- Existing Attachment Selection (shown when "update" is selected) -->
                        <div id="existing_attachment_section" class="form-group mb-4" style="display: none;">
                            <label for="attachment_id" class="form-label">
                                <i class="mdi mdi-folder"></i> Select Existing Location
                            </label>
                            <select id="attachment_id" name="attachment_id" class="form-control">
                                <option value="">Choose location to update...</option>
                                @foreach($attachments as $attachment)
                                <option value="{{ $attachment->id }}">
                                    {{ $attachment->getContextualLocationName() }} 
                                    (Duplicate {{ $attachment->duplicate_number }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- File Preview Section (will be populated via AJAX) -->
                        <div id="preview_section" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="mdi mdi-check-circle"></i> File Preview</h6>
                                <div id="preview_content"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.audits.dashboard', $audit->id) }}" 
                               class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Dashboard
                            </a>
                            
                            <div>
                                <button type="button" 
                                        id="previewBtn" 
                                        class="btn btn-info me-2">
                                    <i class="mdi mdi-eye"></i> Preview File
                                </button>
                                <button type="submit" 
                                        id="importBtn" 
                                        class="btn btn-success" 
                                        disabled>
                                    <i class="mdi mdi-upload"></i> Import Data
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Existing Attachments Info -->
                    @if($attachments->count() > 0)
                    <div class="mt-5">
                        <h6>Current Locations for {{ $reviewType->name }}:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Duplicate #</th>
                                        <th>Location Name</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attachments as $attachment)
                                    <tr>
                                        <td>{{ $attachment->duplicate_number }}</td>
                                        <td>{{ $attachment->getContextualLocationName() }}</td>
                                        <td>
                                            @if($attachment->isMaster())
                                                <span class="badge badge-primary">Master</span>
                                            @else
                                                <span class="badge badge-secondary">Duplicate</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.audits.export-attachment', [$audit->id, $attachment->id]) }}" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="mdi mdi-download"></i> Export
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importModeRadios = document.querySelectorAll('input[name="import_mode"]');
    const newLocationSection = document.getElementById('new_location_section');
    const existingAttachmentSection = document.getElementById('existing_attachment_section');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');
    const fileInput = document.getElementById('excel_file');
    const previewSection = document.getElementById('preview_section');

    // Handle import mode changes
    importModeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'new') {
                newLocationSection.style.display = 'block';
                existingAttachmentSection.style.display = 'none';
                document.getElementById('location_name').required = true;
                document.getElementById('attachment_id').required = false;
            } else {
                newLocationSection.style.display = 'none';
                existingAttachmentSection.style.display = 'block';
                document.getElementById('location_name').required = false;
                document.getElementById('attachment_id').required = true;
            }
        });
    });

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            previewBtn.disabled = false;
            previewSection.style.display = 'none';
            importBtn.disabled = true;
        } else {
            previewBtn.disabled = true;
            importBtn.disabled = true;
        }
    });

    // Handle preview button
    previewBtn.addEventListener('click', function() {
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a file first');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', document.querySelector('[name="_token"]').value);

        this.disabled = true;
        this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Previewing...';

        fetch('{{ route("admin.audits.preview-import", [$audit->id, $reviewType->id]) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let previewHtml = `
                    <p><strong>Total Rows:</strong> ${data.preview.total_rows}</p>
                    <p><strong>Sheets Found:</strong></p>
                    <ul>
                `;
                data.preview.sheets.forEach(sheet => {
                    previewHtml += `<li>${sheet.name}: ${sheet.rows} rows</li>`;
                });
                previewHtml += '</ul>';
                
                document.getElementById('preview_content').innerHTML = previewHtml;
                previewSection.style.display = 'block';
                importBtn.disabled = false;
            } else {
                alert('Preview failed: ' + data.error);
            }
        })
        .catch(error => {
            alert('Preview failed: ' + error.message);
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-eye"></i> Preview File';
        });
    });

    // Handle form submission
    document.getElementById('importForm').addEventListener('submit', function(e) {
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Importing...';
    });
});
</script>
@endpush
@endsection
