{{-- Quick Actions Section --}}
@include('admin.dashboard.components.quick-actions', [
    'actions' => [
        [
            'title' => 'Templates & Questions',
            'url' => route('admin.review-types-crud.index'),
            'class' => 'primary',
            'icon' => 'mdi-file-document-outline',
            'permission' => 'manage review types'
        ],
        [
            'title' => 'Create New Audit',
            'url' => route('admin.audits.create'),
            'class' => 'success',
            'icon' => 'mdi-plus',
            'permission' => 'create audits'
        ],
        [
            'title' => 'Generate Report',
            'url' => route('admin.reports'),
            'class' => 'info',
            'icon' => 'mdi-chart-line',
            'permission' => 'view reports'
        ],
        [
            'title' => 'Enter Audit Code',
            'url' => url('audit-code'),
            'class' => 'warning',
            'icon' => 'mdi-key',
            'role' => 'Auditor'
        ],
        [
            'title' => 'Manage Users',
            'url' => route('admin.users.index'),
            'class' => 'secondary',
            'icon' => 'mdi-account-cog',
            'permission' => 'manage users'
        ],
        [
            'title' => 'Edit Profile',
            'url' => route('admin.users.edit', auth()->id()),
            'class' => 'dark',
            'icon' => 'mdi-account-edit',
        ]
    ]
])
