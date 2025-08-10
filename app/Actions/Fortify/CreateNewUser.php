<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Audit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'string', 'exists:roles,name'],
            'audit_id' => ['nullable', 'required_if:role,Auditor', 'exists:audits,id'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ])->validate();

        $data = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'email_verified_at' => now(), // Auto-verify registered users
        ];

        if (isset($input['profile_photo']) && $input['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['profile_photo_path'] = $input['profile_photo']->store('profile-photos', 'public');
        }

        $user = User::create($data);

        // Assign the selected role
        if (isset($input['role'])) {
            $user->assignRole($input['role']);
        }

        // If user is an auditor and has selected an audit, assign them to it
        if ($input['role'] === 'Auditor' && isset($input['audit_id'])) {
            $user->assignedAudits()->attach($input['audit_id'], [
                'assigned_by' => null, // Self-assigned during registration
                'assigned_at' => now(),
            ]);
        }

        return $user;
    }
}
