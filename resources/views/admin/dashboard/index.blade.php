{{-- Main Dashboard Index --}}
@extends('admin.admin_layout')

@section('title', 'ERA Health Audit Suite - Dashboard')

@section('content')
{{-- Header Section --}}
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">Your ERA Health Audit Suite dashboard overview</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Section --}}
@include('admin.dashboard.admin-stats')
@include('admin.dashboard.auditor-stats')

{{-- Data Tables and Quick Actions Section --}}
<div class="row">
    @include('admin.dashboard.recent-activities')
    @include('admin.dashboard.auditor-audits')
    @include('admin.dashboard.quick-actions')
</div>

{{-- Welcome Message Section --}}
@include('admin.dashboard.components.welcome-message')
@endsection
