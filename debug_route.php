<?php

Route::get('/debug-auditor', function() {
    $user = auth()->user();
    
    if (!$user) {
        return 'Not authenticated';
    }
    
    $html = '<h2>Auditor Debug Info</h2>';
    $html .= '<p><strong>User:</strong> ' . $user->name . ' (' . $user->email . ')</p>';
    $html .= '<p><strong>User ID:</strong> ' . $user->id . '</p>';
    $html .= '<p><strong>Roles:</strong> ' . $user->roles->pluck('name')->join(', ') . '</p>';
    $html .= '<p><strong>Has Auditor role:</strong> ' . ($user->hasRole('Auditor') ? 'YES' : 'NO') . '</p>';
    $html .= '<p><strong>Can view audits:</strong> ' . ($user->can('view audits') ? 'YES' : 'NO') . '</p>';
    $html .= '<p><strong>Assigned audits count:</strong> ' . $user->assignedAudits()->count() . '</p>';
    
    if ($user->assignedAudits()->count() > 0) {
        $html .= '<h3>Assigned Audits:</h3><ul>';
        foreach ($user->assignedAudits as $audit) {
            $html .= '<li>' . $audit->name . ' (ID: ' . $audit->id . ')</li>';
        }
        $html .= '</ul>';
    }
    
    $html .= '<hr>';
    $html .= '<p><a href="' . route('admin.audits.index') . '">Try Admin Audits Index</a></p>';
    
    return $html;
})->middleware(['auth']);
