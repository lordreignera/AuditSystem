@extends('admin.admin_layout')

@section('title', 'Add New Country - Health Audit System')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Add New Country</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Create a new country entry</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left"></i> Back to Countries
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Country Information</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.countries.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Country Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Enter country name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="iso_code" class="form-label">ISO Code (2) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('iso_code') is-invalid @enderror" 
                                       id="iso_code" name="iso_code" value="{{ old('iso_code') }}" 
                                       placeholder="US" maxlength="2" style="text-transform: uppercase;" required>
                                @error('iso_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">2-letter ISO code</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="code" class="form-label">Country Code (3) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
                                       placeholder="USA" maxlength="3" style="text-transform: uppercase;" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">3-letter ISO code</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone_code" class="form-label">Phone Code</label>
                                <div class="input-group">
                                    <span class="input-group-text">+</span>
                                    <input type="text" class="form-control @error('phone_code') is-invalid @enderror" 
                                           id="phone_code" name="phone_code" value="{{ old('phone_code') }}" 
                                           placeholder="1" maxlength="10">
                                </div>
                                @error('phone_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Without the + sign</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency" class="form-label">Currency Code</label>
                                <input type="text" class="form-control @error('currency') is-invalid @enderror" 
                                       id="currency" name="currency" value="{{ old('currency') }}" 
                                       placeholder="USD" maxlength="3" style="text-transform: uppercase;">
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">3-letter currency code</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="flag" class="form-label">Flag Emoji</label>
                                <input type="text" class="form-control @error('flag') is-invalid @enderror" 
                                       id="flag" name="flag" value="{{ old('flag') }}" 
                                       placeholder="🇺🇸" maxlength="255">
                                @error('flag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Flag emoji or image path</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Country
                                    </label>
                                </div>
                                <small class="text-muted">Active countries are available for selection in the system</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="mdi mdi-content-save"></i> Create Country
                        </button>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-cancel"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="mdi mdi-information-outline"></i> Field Information
                </h4>
                
                <div class="mb-3">
                    <h6 class="text-primary">Country Name</h6>
                    <p class="text-muted small mb-0">Full official name of the country</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">ISO Code (2-letter)</h6>
                    <p class="text-muted small mb-0">ISO 3166-1 alpha-2 country code (e.g., US, UK, CA)</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Country Code (3-letter)</h6>
                    <p class="text-muted small mb-0">ISO 3166-1 alpha-3 country code (e.g., USA, GBR, CAN)</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Phone Code</h6>
                    <p class="text-muted small mb-0">International dialing code without the + symbol</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Currency</h6>
                    <p class="text-muted small mb-0">ISO 4217 currency code (e.g., USD, EUR, GBP)</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Flag</h6>
                    <p class="text-muted small mb-0">Flag emoji or path to flag image</p>
                </div>

                <div class="alert alert-info">
                    <small>
                        <i class="mdi mdi-lightbulb-outline"></i>
                        <strong>Tip:</strong> Use standard ISO codes for better compatibility with international systems.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-uppercase certain fields
    document.getElementById('iso_code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    document.getElementById('currency').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush
@endsection
