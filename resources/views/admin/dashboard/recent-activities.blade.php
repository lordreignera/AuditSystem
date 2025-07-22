{{-- Recent Activities Section --}}
@can('view admin dashboard')
    @include('admin.dashboard.components.data-table', [
        'title' => 'Recent Audits',
        'viewAllUrl' => route('admin.audits.index'),
        'headers' => ['Audit Code', 'Name', 'Country', 'Status', 'Start Date', 'End Date'],
        'data' => $recentAudits->map(function($audit) {
            return [
                $audit->review_code,
                $audit->name,
                $audit->country->name ?? '-',
                [
                    'text' => $audit->end_date && $audit->end_date->isPast() ? 'Completed' : 'Active',
                    'class' => $audit->end_date && $audit->end_date->isPast() ? 'success' : 'primary'
                ],
                $audit->start_date ? $audit->start_date->format('M d, Y') : '-',
                $audit->end_date ? $audit->end_date->format('M d, Y') : '-',
            ];
        })->toArray(),
        'emptyMessage' => 'No recent audits found'
    ])
@endcan
