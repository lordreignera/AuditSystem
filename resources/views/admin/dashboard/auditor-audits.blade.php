{{-- Auditor Assigned Audits Section --}}
@role('Auditor')
    @php
        $user = auth()->user();
        $assignedAudits = $user->assignedAudits()->with('country')->limit(5)->get();
        $auditData = [];
        
        foreach($assignedAudits as $audit) {
            $status = now()->gt($audit->end_date) ? 'Completed' : 'In Progress';
            $statusClass = now()->gt($audit->end_date) ? 'success' : 'warning';
            $actionText = now()->gt($audit->end_date) ? 'View' : 'Continue';
            $actionClass = now()->gt($audit->end_date) ? 'info' : 'primary';
            
            $auditData[] = [
                $audit->name,
                $audit->country->name ?? 'N/A',
                'status' => ['text' => $status, 'class' => $statusClass],
                $audit->end_date ? $audit->end_date->format('M d, Y') : 'No deadline',
                'action' => ['url' => route('admin.audits.dashboard', $audit), 'text' => $actionText, 'class' => $actionClass]
            ];
        }
    @endphp
    
    @include('admin.dashboard.components.data-table', [
        'title' => 'My Assigned Audits',
        'viewAllUrl' => route('admin.audits.index'),
        'headers' => ['Audit Name', 'Country', 'Status', 'Deadline', 'Action'],
        'data' => $auditData,
        'emptyMessage' => 'No assigned audits found'
    ])
@endrole
