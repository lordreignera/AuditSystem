<!-- resources/views/admin/admin_layout.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4fd1c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ERA Audit">
    <meta name="msapplication-TileColor" content="#4fd1c7">
    <meta name="msapplication-tap-highlight" content="no">
    
    <title>Health Audit System - @yield('title', 'Dashboard')</title>
    <!-- plugins:css -->
    @include('admin.css')
    <!-- Custom Health Audit System Styles -->
    <style>
        .health-audit-brand {
            color: #2c5282;
            font-weight: 600;
        }
        
        /* Dashboard Card Styles */
        .audit-card, .card {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-left: 4px solid #4fd1c7 !important;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        .audit-card:hover, .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
        
        /* Card Body Styles */
        .card-body {
            background-color: #ffffff !important;
            color: #333333 !important;
        }
        
        /* Text Colors */
        .card-title {
            color: #2d3748 !important;
            font-weight: 600;
        }
        
        .text-muted {
            color: #718096 !important;
        }
        
        /* Statistics Cards */
        .audit-card h3 {
            color: #2d3748 !important;
            font-weight: 700;
        }
        
        /* Table Styles */
        .table {
            background-color: #ffffff !important;
            color: #333333 !important;
        }
        
        .table thead th {
            background-color: #f7fafc !important;
            color: #2d3748 !important;
            border-bottom: 2px solid #e2e8f0 !important;
            font-weight: 600;
        }
        
        .table tbody td {
            background-color: #ffffff !important;
            color: #4a5568 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        
        .table tbody tr:hover {
            background-color: #f7fafc !important;
        }
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #48bb78 !important;
            color: #ffffff !important;
        }
        
        .badge-warning {
            background-color: #ed8936 !important;
            color: #ffffff !important;
        }
        
        .badge-danger {
            background-color: #f56565 !important;
            color: #ffffff !important;
        }
        
        .badge-primary {
            background-color: #4299e1 !important;
            color: #ffffff !important;
        }
        
        /* Icon Styles */
        .icon-box-primary {
            background-color: #4299e1 !important;
            color: #ffffff !important;
        }
        
        .icon-box-success {
            background-color: #48bb78 !important;
            color: #ffffff !important;
        }
        
        .icon-box-warning {
            background-color: #ed8936 !important;
            color: #ffffff !important;
        }
        
        .icon-box-danger {
            background-color: #f56565 !important;
            color: #ffffff !important;
        }
        
        /* Quick Actions */
        .btn {
            font-weight: 500;
        }
        
        /* Role Badge */
        .role-badge {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        /* Content Wrapper */
        .content-wrapper {
            background-color: #f7fafc !important;
        }
        
        /* Welcome Message */
        .welcome-message {
            background-color: #ffffff !important;
            border-left: 4px solid #4fd1c7;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Form Elements */
        .form-control {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid #e2e8f0 !important;
        }
        
        .form-control:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #4fd1c7 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 209, 199, 0.25) !important;
        }
        
        .form-label {
            color: #2d3748 !important;
            font-weight: 500;
        }
        
        /* Fix dropdown issues */
        .navbar .dropdown-menu {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            border-radius: 8px !important;
            padding: 0.5rem 0 !important;
            z-index: 9999 !important;
            display: none !important;
            position: absolute !important;
            min-width: 200px !important;
        }
        
        .navbar .dropdown-menu.show {
            display: block !important;
        }
        
        .navbar .dropdown-item {
            color: #2d3748 !important;
            padding: 0.5rem 1rem !important;
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            background: transparent !important;
            border: none !important;
            width: 100% !important;
            text-align: left !important;
        }
        
        .navbar .dropdown-item:hover {
            background-color: #f7fafc !important;
            color: #2d3748 !important;
        }
        
        .navbar .dropdown-header {
            color: #718096 !important;
            padding: 0.5rem 1rem !important;
        }
        
        .navbar .dropdown-divider {
            border-top: 1px solid #e2e8f0 !important;
            margin: 0.25rem 0 !important;
        }
        
        /* Ensure dropdown toggle works */
        .navbar .nav-link.dropdown-toggle::after {
            display: none !important;
        }
        
        .navbar .dropdown-toggle {
            cursor: pointer !important;
        }
        
        /* Position dropdown correctly */
        .navbar .nav-item.dropdown {
            position: relative !important;
        }
        
        .navbar .dropdown-menu-right {
            right: 0 !important;
            left: auto !important;
        }
    </style>
</head>
<body>
    <!-- Offline Indicator -->
    <div id="offline-indicator" style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; display: none;"></div>
    
    @include('admin.header')
    <!-- partial:partials/_sidebar.html -->
    @include('admin.sidebar')
    <!-- partial -->
    @include('admin.navbar')
    
    <div class="main-panel">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
    
    <!-- partial -->
    <!-- container-scroller -->
    <!-- plugins:js -->
    @include('admin.java')
    <!-- End custom js for this page -->
    
    <!-- Custom page scripts -->
    @stack('scripts')
</body>
</html>
