{{-- Auditor Assigned Audits Section --}}
@role('Auditor')
    @include('admin.dashboard.components.data-table', [
        'title' => 'My Assigned Audits',
        'viewAllUrl' => url('my-audits'),
        'headers' => ['Audit Code', 'Type', 'Status', 'Deadline', 'Action'],
        'data' => [
            [
                'AUD-2025-004',
                'Health Facility',
                'status' => ['text' => 'In Progress', 'class' => 'warning'],
                now()->addDays(5)->format('M d, Y'),
                'action' => ['url' => '#', 'text' => 'Continue', 'class' => 'primary']
            ],
            [
                'AUD-2025-005',
                'District Health',
                'status' => ['text' => 'Not Started', 'class' => 'info'],
                now()->addDays(10)->format('M d, Y'),
                'action' => ['url' => '#', 'text' => 'Start', 'class' => 'success']
            ]
        ],
        'emptyMessage' => 'No assigned audits found'
    ])
@endrole
