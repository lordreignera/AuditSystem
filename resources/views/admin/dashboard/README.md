# Dashboard Structure Overview

This document outlines the new refactored dashboard structure for the Health Audit System.

## Directory Structure
```
resources/views/admin/dashboard/
├── index.blade.php                 # Main dashboard layout
├── admin-stats.blade.php          # Admin statistics section
├── auditor-stats.blade.php        # Auditor statistics section  
├── recent-activities.blade.php    # Recent activities section
├── auditor-audits.blade.php       # Auditor assigned audits section
├── quick-actions.blade.php        # Quick actions section
└── components/
    ├── stat-card.blade.php         # Reusable statistics card
    ├── data-table.blade.php        # Reusable data table
    ├── quick-actions.blade.php     # Quick actions component
    └── welcome-message.blade.php   # Welcome message component
```

## Component Usage

### Stat Card Component
```php
@include('admin.dashboard.components.stat-card', [
    'value' => 25,
    'title' => 'Total Audits',
    'icon' => 'mdi-clipboard-text',
    'iconClass' => 'primary',
    'colSize' => '3' // Optional, defaults to 3
])
```

### Data Table Component
```php
@include('admin.dashboard.components.data-table', [
    'title' => 'Recent Activities',
    'viewAllUrl' => '#',
    'headers' => ['Code', 'Type', 'Status'],
    'data' => [
        ['AUD-001', 'District', 'status' => ['text' => 'Active', 'class' => 'success']]
    ],
    'emptyMessage' => 'No data available'
])
```

### Quick Actions Component
```php
@include('admin.dashboard.components.quick-actions', [
    'actions' => [
        [
            'title' => 'Create Audit',
            'url' => url('audits/create'),
            'class' => 'primary',
            'icon' => 'mdi-plus',
            'permission' => 'create audits'
        ]
    ]
])
```

## Benefits

1. **Maintainability**: Components are separated and reusable
2. **Readability**: Main dashboard file is now very clean and easy to understand  
3. **Reusability**: Components can be used in other parts of the system
4. **Modularity**: Easy to add/remove sections without affecting others
5. **Testing**: Individual components can be tested separately
6. **Performance**: Only load what's needed based on user roles/permissions

## Migration Notes

- Old dashboard file is backed up as `dashboard-old.blade.php`
- All functionality remains the same
- Role-based permissions are preserved
- Styling and icons are maintained
