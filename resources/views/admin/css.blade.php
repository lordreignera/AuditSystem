<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>AUDIT PROGRAM SYSTEM</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <!-- Bootstrap 5 CSS for modals -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/jvectormap/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/owl-carousel-2/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/owl-carousel-2/owl.theme.default.min.css') }}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/icon1.png') }}" />
   <style>

    body {
        background-color: #f7fafc; /* Light gray background */
    }
    
    .sidebar {
        background-color: #000f89 !important; /* Blue sidebar */
        color: #ffffff !important; /* White text */
    }

    .navbar {
        background: linear-gradient(90deg, #0a183d 0%, #1a237e 100%) !important; /* Modern dark blue gradient */
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .main-panel {
        background-color: #f7fafc !important; /* Light gray background */
        color: #000000 !important; /* Black text */
    }

    .content-wrapper {
        background-color: #f7fafc !important; /* Light gray background */
        color: #000000 !important; /* Black text */
        padding: 20px;
    }

    /* Dashboard Cards - Force White Background */
    .card, .audit-card {
        background-color: #ffffff !important; /* White background */
        color: #333333 !important; /* Dark text */
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        margin-bottom: 20px;
    }

    .card-body {
        background-color: #ffffff !important; /* White background */
        color: #333333 !important; /* Dark text */
        padding: 20px;
    }

    /* Card Titles and Text */
    .card-title {
        color: #2d3748 !important; /* Dark gray */
        font-weight: 600;
        margin-bottom: 15px;
    }

    /* Statistics Numbers */
    .card h1, .card h2, .card h3, .card h4, .card h5, .card h6 {
        color: #2d3748 !important; /* Dark gray */
    }

    /* Tables */
    .table {
        background-color: #ffffff !important;
        color: #333333 !important;
    }

    .table thead th {
        background-color: #f7fafc !important;
        color: #2d3748 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        font-weight: 600;
        padding: 12px 15px;
    }

    .table tbody td {
        background-color: #ffffff !important;
        color: #4a5568 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 12px 15px;
    }

    .table tbody tr:hover {
        background-color: #f7fafc !important;
    }

    /* Text Colors */
    .content-wrapper h1,
    .content-wrapper h2,
    .content-wrapper h3,
    .content-wrapper h4,
    .content-wrapper h5,
    .content-wrapper h6,
    .content-wrapper p,
    .content-wrapper span,
    .content-wrapper div {
        color: #333333 !important;
    }

    .content-wrapper a {
        color: #4299e1 !important;
    }

    .text-muted {
        color: #718096 !important;
    }

    /* Buttons */
    .btn {
        font-weight: 500;
        border-radius: 6px;
        padding: 8px 16px;
    }

    /* List Groups */
    .list-group-item {
        background-color: #ffffff !important;
        color: #333333 !important;
        border: 1px solid #e2e8f0 !important;
    }

    /* Forms */
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

    /* Alert Messages */
    .alert {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
    }

    /* Icons */
    .icon {
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
    }

    .card-header {
        background-color: #f7fafc !important;
        color: #2d3748 !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    /* Override any dark theme styles */
    * {
        color: inherit !important;
    }

    /* Force Table Text Visibility - Highest Priority */
    .data-table-container .table,
    .data-table-container .table tbody,
    .data-table-container .table tbody tr,
    .data-table-container .table tbody td {
        background-color: #ffffff !important;
        color: #333333 !important;
    }

    .data-table-container .table tbody td {
        font-weight: 500 !important;
        padding: 12px 15px !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .data-table-container .table thead th {
        background-color: #f8f9fa !important;
        color: #495057 !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #dee2e6 !important;
        padding: 12px 15px !important;
    }

    /* Force text color in all table elements */
    .data-table-container table,
    .data-table-container table *:not(.badge):not(.btn) {
        color: #333333 !important;
    }

    /* Force ALL table content to be visible regardless of theme */
    .table,
    .table *,
    .table td,
    .table th,
    .table tbody td,
    .table thead th {
        color: #333333 !important;
        background-color: #ffffff !important;
    }

    .table thead th {
        background-color: #f8f9fa !important;
        color: #495057 !important;
    }

    /* Override dark theme table styles */
    .main-panel .table,
    .main-panel .table td,
    .main-panel .table th,
    .content-wrapper .table,
    .content-wrapper .table td,
    .content-wrapper .table th,
    .card-body .table,
    .card-body .table td,
    .card-body .table th {
        color: #333333 !important;
        background-color: #ffffff !important;
    }

    </style>
