<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            ReviewTypeSeeder::class,
            ExcelAuditTemplateSeeder::class,
            CountrySeeder::class,
            AuditSeeder::class,


            // Add other seeders here as needed
        ]);

        $this->command->info('🎉 Health Audit System database seeded successfully!');
        $this->command->info('📋 Your Laravel Health Audit System is ready to use.');
    }
}
