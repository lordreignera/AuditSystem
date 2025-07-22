{{-- Auditor Statistics Section --}}
@role('Auditor')
<div class="row">
    @include('admin.dashboard.components.stat-card', [
        'value' => $myAssignedAudits ?? 0,
        'title' => 'My Assigned Audits',
        'icon' => 'mdi-clipboard-account',
        'iconClass' => 'primary',
        'colSize' => '6'
    ])

    @include('admin.dashboard.components.stat-card', [
        'value' => $myCompletedAudits ?? 0,
        'title' => 'My Completed Audits',
        'icon' => 'mdi-check-all',
        'iconClass' => 'success',
        'colSize' => '6'
    ])
</div>
@endrole
