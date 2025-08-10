<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manage users']);
    }

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.user-management.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        $audits = Audit::select('id', 'name', 'description', 'start_date', 'end_date')
                      ->orderBy('start_date', 'desc')
                      ->get();
        return view('admin.user-management.users.create', compact('roles', 'audits'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
            'audits' => 'array',
            'audits.*' => 'exists:audits,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Auto-verify for admin created users
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user = User::create($data);

        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        // Assign audits if user has Auditor role and audits are selected
        if ($request->has('audits') && in_array('Auditor', $request->roles ?? [])) {
            foreach ($request->audits as $auditId) {
                $user->assignedAudits()->attach($auditId, [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }
        }

        $message = 'User created successfully and assigned roles.';
        if ($request->has('audits') && count($request->audits) > 0) {
            $message .= ' Audit assignments completed.';
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles.permissions');
        return view('admin.user-management.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $audits = Audit::select('id', 'name', 'description', 'start_date', 'end_date')
                      ->orderBy('start_date', 'desc')
                      ->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        $userAudits = $user->assignedAudits->pluck('id')->toArray();
        return view('admin.user-management.users.edit', compact('user', 'roles', 'audits', 'userRoles', 'userAudits'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
            'audits' => 'array',
            'audits.*' => 'exists:audits,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($data);

        // Sync roles
        $user->syncRoles($request->roles ?? []);

        // Sync audit assignments if user has Auditor role
        if (in_array('Auditor', $request->roles ?? [])) {
            $auditData = [];
            foreach ($request->audits ?? [] as $auditId) {
                $auditData[$auditId] = [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ];
            }
            $user->assignedAudits()->sync($auditData);
        } else {
            // Remove all audit assignments if not an auditor
            $user->assignedAudits()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }
}
