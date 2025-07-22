{{-- Admin Statistics Section --}}
@can('view admin dashboard')
<div class="row">
    @include('admin.dashboard.components.stat-card', [
        'value' => $totalAudits ?? 0,
        'title' => 'Total Audits',
        'icon' => 'mdi-clipboard-text',
        'iconClass' => 'primary'
    ])

    @include('admin.dashboard.components.stat-card', [
        'value' => $activeAudits ?? 0,
        'title' => 'Active Audits',
        'icon' => 'mdi-play',
        'iconClass' => 'success'
    ])

    @include('admin.dashboard.components.stat-card', [
        'value' => $totalUsers ?? 0,
        'title' => 'System Users',
        'icon' => 'mdi-account-multiple',
        'iconClass' => 'warning'
    ])

    @include('admin.dashboard.components.stat-card', [
        'value' => $completedAudits ?? 0,
        'title' => 'Completed Audits',
        'icon' => 'mdi-check-circle',
        'iconClass' => 'danger'
    ])
</div>
@endcan
