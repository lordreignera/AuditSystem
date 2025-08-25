<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = Country::where('is_active', true)->get();
        
        if ($countries->isEmpty()) {
            $this->command->error('No active countries found. Please run CountrySeeder first.');
            return;
        }

        // Helper function to get country id by iso_code or fallback to first country
        function getCountryId($countries, $isoCode) {
            $country = $countries->where('iso_code', $isoCode)->first();
            return $country ? $country->id : $countries->first()->id;
        }

        $audits = [
            [
                'name' => 'Healthcare System Assessment - Uganda',
                'description' => 'Comprehensive evaluation of healthcare delivery systems, infrastructure, and service quality across national health facilities.',
                'country_id' => getCountryId($countries, 'UG'),
                'participants' => ['Dr. Sarah Johnson', 'Prof. Michael Asante', 'Ms. Grace Nakamura'],
                'start_date' => Carbon::now()->addDays(10),
                'duration_value' => 3,
                'duration_unit' => 'months',
            ],
            [
                'name' => 'Financial Management Audit - Sierra Leone',
                'description' => 'Review of financial controls, procurement processes, and budget utilization in public health institutions.',
                'country_id' => getCountryId($countries, 'SL'),
                'participants' => ['Mr. David Thompson', 'Mrs. Fatima Sesay'],
                'start_date' => Carbon::now()->addDays(5),
                'duration_value' => 45,
                'duration_unit' => 'days',
            ],
            [
                'name' => 'Digital Health Infrastructure Review - Kenya',
                'description' => 'Assessment of electronic health records, telemedicine capabilities, and digital health technology adoption.',
                'country_id' => getCountryId($countries, 'KE'),
                'participants' => ['Dr. James Mwangi', 'Ms. Catherine Wanjiku', 'Mr. Peter Ochieng'],
                'start_date' => Carbon::now()->addDays(-15),
                'duration_value' => 2,
                'duration_unit' => 'months',
            ],
            [
                'name' => 'Emergency Preparedness Assessment - Nigeria',
                'description' => 'Evaluation of emergency response systems, disaster preparedness, and crisis management protocols.',
                'country_id' => getCountryId($countries, 'NG'),
                'participants' => ['Dr. Adaora Okafor', 'Mr. Ibrahim Musa', 'Dr. Chioma Eze', 'Prof. Yusuf Ahmed'],
                'start_date' => Carbon::now()->addDays(20),
                'duration_value' => 6,
                'duration_unit' => 'months',
            ],
            [
                'name' => 'Primary Healthcare Delivery Audit - Ghana',
                'description' => 'Review of community health services, preventive care programs, and primary healthcare accessibility.',
                'country_id' => getCountryId($countries, 'GH'),
                'participants' => ['Dr. Kwame Asante', 'Ms. Akosua Mensah'],
                'start_date' => Carbon::now()->addDays(-5),
                'duration_value' => 90,
                'duration_unit' => 'days',
            ],
            [
                'name' => 'Mental Health Services Review - Rwanda',
                'description' => 'Assessment of mental health service delivery, community-based interventions, and healthcare worker training.',
                'country_id' => getCountryId($countries, 'RW'),
                'participants' => ['Dr. Marie Uwimana', 'Mr. Jean Baptiste Habimana', 'Ms. Josephine Mukamana'],
                'start_date' => Carbon::now()->addDays(30),
                'duration_value' => 4,
                'duration_unit' => 'months',
            ]
        ];

        foreach ($audits as $auditData) {
            // Generate unique review code
            $auditData['review_code'] = Audit::generateReviewCode();
            $auditData['created_by'] = 1; // or another valid user ID
            Audit::create($auditData);
        }

        $this->command->info('Sample audits seeded successfully!');
    }
}
