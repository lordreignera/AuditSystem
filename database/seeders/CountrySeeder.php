<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Uganda',
                'code' => 'UGA',
                'iso_code' => 'UG',
                'phone_code' => '256',
                'currency' => 'UGX',
                'is_active' => true,
            ],
            [
                'name' => 'Sierra Leone',
                'code' => 'SLE',
                'iso_code' => 'SL',
                'phone_code' => '232',
                'currency' => 'SLL',
                'is_active' => true,
            ],
            [
                'name' => 'Kenya',
                'code' => 'KEN',
                'iso_code' => 'KE',
                'phone_code' => '254',
                'currency' => 'KES',
                'is_active' => true,
            ],
            [
                'name' => 'Nigeria',
                'code' => 'NGA',
                'iso_code' => 'NG',
                'phone_code' => '234',
                'currency' => 'NGN',
                'is_active' => true,
            ],
            [
                'name' => 'Ghana',
                'code' => 'GHA',
                'iso_code' => 'GH',
                'phone_code' => '233',
                'currency' => 'GHS',
                'is_active' => true,
            ],
            [
                'name' => 'Tanzania',
                'code' => 'TZA',
                'iso_code' => 'TZ',
                'phone_code' => '255',
                'currency' => 'TZS',
                'is_active' => true,
            ],
            [
                'name' => 'Rwanda',
                'code' => 'RWA',
                'iso_code' => 'RW',
                'phone_code' => '250',
                'currency' => 'RWF',
                'is_active' => true,
            ],
            [
                'name' => 'South Africa',
                'code' => 'ZAF',
                'iso_code' => 'ZA',
                'phone_code' => '27',
                'currency' => 'ZAR',
                'is_active' => true,
            ],

        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['iso_code' => $country['iso_code']],
                $country
            );
        }

        $this->command->info('Sample countries seeded successfully!');
    }
}
