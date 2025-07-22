<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewType;

class ReviewTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviewTypes = [
            [
                'name' => 'National',
                'description' => 'National level health audit review covering country-wide health system assessments',
                'is_active' => true,
            ],
            [
                'name' => 'Province/region',
                'description' => 'Provincial level health audit review for regional health system evaluation',
                'is_active' => true,
            ],
            [
                'name' => 'District',
                'description' => 'District level health audit review focusing on local health service delivery',
                'is_active' => true,
            ],
            [
                'name' => 'Health Facility',
                'description' => 'Health facility level audit review for individual healthcare institutions',
                'is_active' => true,
            ],
        ];

        foreach ($reviewTypes as $reviewType) {
            ReviewType::firstOrCreate(
                ['name' => $reviewType['name']],
                $reviewType
            );
        }

        $this->command->info('Review types seeded successfully!');
    }
}
