<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExcelImportExportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------       // Route::get('audits/{audit}/import-form/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'showImportForm'])
           // ->name('admin.audits.show-import-form');
       // Route::post('audits/{audit}/import-excel/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'importExcel'])
           // ->name('admin.audits.import-excel');
           
       // Debug route for template structure
       Route::get('audits/{audit}/debug-template/{reviewType}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'debugTemplateStructure'])
           ->name('admin.audits.debug-template');--------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Debug route to check authentication status without any middleware
Route::get('/debug-auth-status', function () {
    $html = '<h2>Authentication Debug Info</h2>';
    $html .= '<p><strong>Auth Check:</strong> ' . (auth()->check() ? 'YES' : 'NO') . '</p>';
    $html .= '<p><strong>Session ID:</strong> ' . session()->getId() . '</p>';
    $html .= '<p><strong>Current URL:</strong> ' . request()->url() . '</p>';
    
    if (auth()->check()) {
        $user = auth()->user();
        $html .= '<p><strong>User:</strong> ' . $user->name . ' (' . $user->email . ')</p>';
        $html .= '<p><strong>User ID:</strong> ' . $user->id . '</p>';
        $html .= '<p><strong>Roles:</strong> ' . $user->roles->pluck('name')->join(', ') . '</p>';
        $html .= '<p><strong>Has manage users permission:</strong> ' . ($user->can('manage users') ? 'YES' : 'NO') . '</p>';
        $html .= '<p><strong>Has manage roles permission:</strong> ' . ($user->can('manage roles') ? 'YES' : 'NO') . '</p>';
        $html .= '<p><strong>Has manage permissions permission:</strong> ' . ($user->can('manage permissions') ? 'YES' : 'NO') . '</p>';
    } else {
        $html .= '<p style="color: red;"><strong>Not Authenticated!</strong> Please <a href="' . route('login') . '">login first</a></p>';
        $html .= '<div style="background: #f7fafc; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        $html .= '<h4>Demo Login Credentials:</h4>';
        $html .= '<ul>';
        $html .= '<li><strong>Super Admin:</strong> superadmin@audit.com / SuperAdmin123!</li>';
        $html .= '<li><strong>Admin:</strong> admin@audit.com / Admin123!</li>';
        $html .= '<li><strong>Audit Manager:</strong> manager@audit.com / Manager123!</li>';
        $html .= '<li><strong>Auditor:</strong> auditor@audit.com / Auditor123!</li>';
        $html .= '</ul>';
        $html .= '</div>';
    }
    
    $html .= '<hr>';
    $html .= '<h3>Quick Links:</h3>';
    $html .= '<ul>';

// Debug route for auditor
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
    $html .= '<li><a href="' . route('login') . '">Login</a></li>';
    if (auth()->check()) {
        $html .= '<li><a href="/admin/users">Admin Users</a></li>';
        $html .= '<li><a href="/admin/roles">Admin Roles</a></li>';
        $html .= '<li><a href="/admin/permissions">Admin Permissions</a></li>';
        $html .= '<li><a href="/admin/countries">Countries Management</a></li>';
        $html .= '<li><a href="/admin/test">Admin Test</a></li>';
        $html .= '<li><a href="' . route('admin.dashboard') . '">Dashboard</a></li>';
    }
    $html .= '<li><a href="/logout-page">Logout</a></li>';
    $html .= '</ul>';
    return $html;
});



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Redirect dashboard to admin home
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
    
    // Admin Dashboard with role-based access

// Admin Dashboard with role-based access
    Route::get('/home', function () {
        $user = auth()->user();

        $totalAudits = \App\Models\Audit::count();
        $activeAudits = \App\Models\Audit::where('end_date', '>', now())->count();
        $completedAudits = \App\Models\Audit::where('end_date', '<=', now())->count();
        $totalUsers = \App\Models\User::count();

        // For auditors (using assignedAudits relationship)
        $myAssignedAudits = $user->hasRole('Auditor')
            ? $user->assignedAudits()->count()
            : 0;
        $myCompletedAudits = $user->hasRole('Auditor')
            ? $user->assignedAudits()->where('end_date', '<=', now())->count()
            : 0;

        // Recent audits for the table - filter for auditors to show only assigned audits
        if ($user->hasRole('Auditor')) {
            $recentAudits = $user->assignedAudits()->with('country')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        } else {
            $recentAudits = \App\Models\Audit::with('country')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact(
            'totalAudits', 'activeAudits', 'completedAudits', 'totalUsers',
            'myAssignedAudits', 'myCompletedAudits', 'recentAudits'
        ));
    })->name('admin.dashboard');
    
    // Admin routes
    Route::prefix('admin')->group(function () {
        // Test route to verify auth is working
        Route::get('/test', function () {
            return 'Admin test route works! User: ' . auth()->user()->name;
        });
        
        // Test route to check is_active field
        Route::get('/test-users', function () {
            $users = \App\Models\User::select('name', 'email', 'is_active')->get();
            $html = '<h3>Users and their status:</h3><ul>';
            foreach ($users as $user) {
                $status = $user->is_active ? 'Active' : 'Inactive';
                $html .= '<li>' . $user->name . ' (' . $user->email . ') - ' . $status . '</li>';
            }
            $html .= '</ul>';
            return $html;
        });
        
        // User Management Routes
        Route::resource('users', \App\Http\Controllers\Admin\UserManagement\UserController::class, [
            'as' => 'admin'
        ]);
        Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserManagement\UserController::class, 'toggleStatus'])
            ->name('admin.users.toggle-status');
        
        // Role Management Routes
        Route::resource('roles', \App\Http\Controllers\Admin\UserManagement\RoleController::class, [
            'as' => 'admin'
        ]);
        
        // Permission Management Routes
        Route::resource('permissions', \App\Http\Controllers\Admin\UserManagement\PermissionController::class, [
            'as' => 'admin'
        ]);
        
        // Country Management Routes
        Route::resource('countries', \App\Http\Controllers\Admin\SystemData\CountryController::class, [
            'as' => 'admin'
        ]);
        Route::patch('countries/{country}/toggle-status', [\App\Http\Controllers\Admin\SystemData\CountryController::class, 'toggleStatus'])
            ->name('admin.countries.toggle-status');
        
        // Audit Management Routes
        Route::resource('audits', \App\Http\Controllers\Admin\AuditController::class, [
            'as' => 'admin'
        ]);
        
        // Additional audit routes
        Route::get('audits/{audit}/dashboard', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'dashboard'])
            ->name('admin.audits.dashboard');
        Route::post('audits/{audit}/attach-review-type', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'attachReviewType'])
            ->name('admin.audits.attach-review-type');
        Route::post('audits/{audit}/detach-review-type', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'detachReviewType'])
            ->name('admin.audits.detach-review-type');
        // Sync audit table structures with default template
        Route::post('audits/{audit}/sync-table-structures/{reviewTypeId}', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'syncAuditTableStructures'])
            ->name('admin.audits.sync-table-structures');
        
        // Section management routes
        Route::post('audits/{audit}/add-section', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'addSection'])
            ->name('admin.audits.add-section');
        Route::put('audits/{audit}/update-section', [\App\Http\Controllers\Admin\AuditController::class, 'updateSection'])
            ->name('admin.audits.update-section');
        Route::delete('audits/{audit}/delete-section', [\App\Http\Controllers\Admin\AuditController::class, 'deleteSection'])
            ->name('admin.audits.delete-section');
        
        // Question management routes
        Route::post('audits/{audit}/add-question', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'addQuestion'])
            ->name('admin.audits.add-question');
        Route::put('audits/{audit}/update-question', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'updateQuestion'])
            ->name('admin.audits.update-question');
        Route::delete('audits/{audit}/delete-question', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'deleteQuestion'])
            ->name('admin.audits.delete-question');
        
        // Template management routes
        Route::put('audits/{audit}/update-template', [\App\Http\Controllers\Admin\AuditController::class, 'updateTemplate'])
            ->name('admin.audits.update-template');
        Route::post('audits/{audit}/duplicate-template', [\App\Http\Controllers\Admin\AuditController::class, 'duplicateTemplate'])
            ->name('admin.audits.duplicate-template');
        Route::post('audits/{audit}/duplicate-review-type', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'duplicateReviewType'])
            ->name('admin.audits.duplicate-review-type');
        Route::post('audits/{audit}/rename-facility', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'renameFacility'])
            ->name('admin.audits.rename-facility');
        Route::post('audits/{audit}/rename-location', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'renameLocation'])
            ->name('admin.audits.rename-location');
        Route::post('audits/{audit}/remove-duplicate', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'removeDuplicate'])
            ->name('admin.audits.remove-duplicate');
        Route::get('audits/{audit}/load-sections', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'loadSections'])
            ->name('admin.audits.load-sections');
        
        // Excel Import/Export routes
        //Route::get('audits/{audit}/export-attachment/{attachmentId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'exportAttachment'])
            //->name('admin.audits.export-attachment');
       // Route::get('audits/{audit}/download-blank-template/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'downloadBlankTemplate'])
           // ->name('admin.audits.download-blank-template');
       // Route::get('audits/{audit}/import-excel', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'showGeneralImportForm'])
           // ->name('admin.audits.import-excel');
       // Route::get('audits/{audit}/import-form/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'showImportForm'])
           // ->name('admin.audits.show-import-form');
       // Route::post('audits/{audit}/import-excel/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'importExcel'])
           // ->name('admin.audits.process-import-excel');
        //Route::post('audits/{audit}/preview-import/{reviewTypeId}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'previewImport'])
            //->name('admin.audits.preview-import');
        
        // API routes for AJAX requests
        Route::get('api/templates/{template}', function(\App\Models\Template $template) {
            return $template->load('reviewType');
        });



        // Export XLSX booklet for a specific attachment (location)
        Route::get('admin/audits/{audit}/attachments/{attachment}/export-booklet',
            [ExcelImportExportController::class, 'exportAttachmentXlsx']
        )->name('admin.attachments.export.booklet');

        // Import XLSX booklet for a review type (new location or update an existing attachment)
        Route::post('admin/audits/{audit}/review-types/{reviewType}/import-booklet',
            [ExcelImportExportController::class, 'importBooklet']
        )->name('admin.reviewtypes.import.booklet');

        // Add these routes in your admin group
        Route::get('audits/{audit}/attachments/{attachment}/export-excel', 
            [ExcelImportExportController::class, 'exportAttachmentXlsx'])
            ->name('admin.audits.export-attachment-excel');

        Route::get('audits/{audit}/review-types/{reviewType}/import-form', 
            [ExcelImportExportController::class, 'showImportForm'])
            ->name('admin.audits.show-import-form');

        Route::post('audits/{audit}/review-types/{reviewType}/import-excel', 
            [ExcelImportExportController::class, 'importBooklet'])
            ->name('admin.audits.import-booklet');
            
        Route::get('api/sections/{section}', function(\App\Models\Section $section) {
            return $section->load('template');
        });
        
        Route::get('api/questions/{question}', function(\App\Models\Question $question) {
            return $question->load('section');
        });
        
        Route::get('api/review-types/{reviewType}/templates', function(\App\Models\ReviewType $reviewType) {
            return $reviewType->templates()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        });
        
        // Reports Routes
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('reports/{audit}', [\App\Http\Controllers\Admin\ReportController::class, 'show'])->name('admin.reports.show');
        Route::post('reports/{audit}/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generateAiReport'])->name('admin.reports.generate');
        
        // Review Types management
        Route::resource('review-types', \App\Http\Controllers\Admin\ReviewTypeController::class, [
            'as' => 'admin'
        ]);
        
        // Template Management Routes (Default Templates)
        Route::resource('templates', \App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, [
            'as' => 'admin'
        ]);
        Route::post('templates/{template}/add-section', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'addSection'])
            ->name('admin.templates.add-section');
        Route::put('templates/{template}/update-section', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'updateSection'])
            ->name('admin.templates.update-section');
        Route::delete('templates/{template}/delete-section', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'deleteSection'])
            ->name('admin.templates.delete-section');
        Route::post('templates/{template}/add-question', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'addQuestion'])
            ->name('admin.templates.add-question');
        Route::put('templates/{template}/update-question', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'updateQuestion'])
            ->name('admin.templates.update-question');
        Route::delete('templates/{template}/delete-question', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'deleteQuestion'])
            ->name('admin.templates.delete-question');
        Route::post('templates/{template}/duplicate', [\App\Http\Controllers\Admin\TemplateManagement\TemplateController::class, 'duplicate'])
            ->name('admin.templates.duplicate');
        
        // Review Types CRUD with Template Structure
        Route::prefix('review-types-crud')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReviewTypeCrudController::class, 'index'])->name('admin.review-types-crud.index');
            Route::get('/{reviewType}', [\App\Http\Controllers\Admin\ReviewTypeCrudController::class, 'show'])->name('admin.review-types-crud.show');
            Route::get('/{reviewType}/template/{template}/create-audit', [\App\Http\Controllers\Admin\ReviewTypeCrudController::class, 'createAudit'])->name('admin.review-types-crud.create-audit');
            Route::post('/{reviewType}/template/{template}/store-audit', [\App\Http\Controllers\Admin\ReviewTypeCrudController::class, 'storeAudit'])->name('admin.review-types-crud.store-audit');
        });

        Route::post('/{reviewType}/save-responses', [\App\Http\Controllers\Admin\AuditManagement\AuditController::class, 'storeResponses'])
    ->name('admin.review-types-crud.save-responses');
        
        // Debug routes
        Route::get('/debug-user', function () {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Not authenticated']);
            }
            
            return response()->json([
                'user' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'can_manage_users' => $user->can('manage users'),
                'can_view_admin_dashboard' => $user->can('view admin dashboard'),
            ]);
        })->name('debug.user');
        
        Route::get('/test-auth', function () {
            if (auth()->check()) {
                return 'You are logged in as: ' . auth()->user()->name;
            } else {
                return 'You are not logged in. Please go to <a href="/login">/login</a>';
            }
        })->name('test.auth');
    });
    
    // Auditor specific routes (temporarily disabled for testing)
    /*
    Route::middleware('role:Auditor')->group(function () {
        Route::get('/my-audits', function () {
            return view('admin.auditor.my-audits');
        })->name('auditor.my-audits');
        
        Route::get('/audit-code', function () {
            return view('admin.auditor.audit-code');
        })->name('auditor.audit-code');
    });
    */
});
