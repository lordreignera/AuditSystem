<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating default templates for review types...');

        // Get all review types
        $reviewTypes = ReviewType::all();

        foreach ($reviewTypes as $reviewType) {
            $this->createTemplatesForReviewType($reviewType);
        }

        $this->command->info('Templates seeded successfully!');
    }

    private function createTemplatesForReviewType(ReviewType $reviewType)
    {
        // Create templates based on review type
        $templates = $this->getTemplatesForReviewType($reviewType->name);

        foreach ($templates as $templateData) {
            $template = Template::create([
                'review_type_id' => $reviewType->id,
                'name' => $templateData['name'],
                'description' => $templateData['description'],
                'is_default' => true,
                'is_active' => true,
            ]);

            // Create sections for this template
            foreach ($templateData['sections'] as $sectionData) {
                $section = Section::create([
                    'template_id' => $template->id,
                    'name' => $sectionData['name'],
                    'description' => $sectionData['description'],
                    'order' => $sectionData['order_number'],
                    'is_active' => true,
                ]);

                // Create questions for this section
                foreach ($sectionData['questions'] as $questionData) {
                    Question::create([
                        'section_id' => $section->id,
                        'question_text' => $questionData['question_text'],
                        'response_type' => $questionData['question_type'],
                        'is_required' => $questionData['is_required'] ?? false,
                        'order' => $questionData['order_number'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    private function getTemplatesForReviewType($reviewTypeName)
    {
        switch ($reviewTypeName) {
            case 'National':
                return [
                    [
                        'name' => 'National Health System Assessment',
                        'description' => 'Comprehensive assessment of national health system performance',
                        'sections' => [
                            [
                                'name' => 'Health System Governance',
                                'description' => 'Assessment of national health governance structures',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Is there a national health policy framework in place?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'Describe the national health governance structure',
                                        'question_type' => 'textarea',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                    [
                                        'question_text' => 'What is the annual health budget allocation?',
                                        'question_type' => 'text',
                                        'is_required' => false,
                                        'order_number' => 3,
                                    ],
                                ]
                            ],
                            [
                                'name' => 'Health Service Delivery',
                                'description' => 'Evaluation of health service delivery systems',
                                'order_number' => 2,
                                'questions' => [
                                    [
                                        'question_text' => 'What is the coverage of primary healthcare services?',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'Rate the quality of health service delivery',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => 'National Health Workforce Assessment',
                        'description' => 'Assessment of national health workforce capacity and performance',
                        'sections' => [
                            [
                                'name' => 'Workforce Planning',
                                'description' => 'Evaluation of health workforce planning and distribution',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Is there a national health workforce plan?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'What is the doctor-to-population ratio?',
                                        'question_type' => 'text',
                                        'is_required' => false,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                        ]
                    ],
                ];

            case 'Province/region':
                return [
                    [
                        'name' => 'Provincial Health System Review',
                        'description' => 'Review of provincial health system performance and outcomes',
                        'sections' => [
                            [
                                'name' => 'Provincial Health Management',
                                'description' => 'Assessment of provincial health management structures',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Is there a provincial health strategic plan?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'Describe the provincial health management structure',
                                        'question_type' => 'textarea',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                            [
                                'name' => 'Health Service Coverage',
                                'description' => 'Evaluation of health service coverage in the province',
                                'order_number' => 2,
                                'questions' => [
                                    [
                                        'question_text' => 'What percentage of the population has access to health services?',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                ]
                            ],
                        ]
                    ],
                ];

            case 'District':
                return [
                    [
                        'name' => 'District Health System Assessment',
                        'description' => 'Assessment of district health system performance and service delivery',
                        'sections' => [
                            [
                                'name' => 'District Health Management',
                                'description' => 'Assessment of district health management capacity',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Is there a district health management team?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'How many health facilities are in the district?',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                            [
                                'name' => 'Community Health Services',
                                'description' => 'Evaluation of community health service delivery',
                                'order_number' => 2,
                                'questions' => [
                                    [
                                        'question_text' => 'Are community health workers deployed in the district?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                ]
                            ],
                        ]
                    ],
                ];

            case 'Health Facility':
                return [
                    [
                        'name' => 'Health Facility Quality Assessment',
                        'description' => 'Comprehensive quality assessment of health facility services',
                        'sections' => [
                            [
                                'name' => 'Infrastructure & Equipment',
                                'description' => 'Assessment of facility infrastructure and equipment',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Is the facility infrastructure in good condition?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'List the major equipment available',
                                        'question_type' => 'textarea',
                                        'is_required' => false,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                            [
                                'name' => 'Staff & Service Delivery',
                                'description' => 'Assessment of staff capacity and service delivery quality',
                                'order_number' => 2,
                                'questions' => [
                                    [
                                        'question_text' => 'How many qualified health workers are employed?',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'Rate the overall service delivery quality',
                                        'question_type' => 'text',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => 'Patient Safety & Infection Control',
                        'description' => 'Assessment of patient safety measures and infection control practices',
                        'sections' => [
                            [
                                'name' => 'Infection Prevention',
                                'description' => 'Evaluation of infection prevention and control measures',
                                'order_number' => 1,
                                'questions' => [
                                    [
                                        'question_text' => 'Are hand hygiene stations available and functional?',
                                        'question_type' => 'yes_no',
                                        'is_required' => true,
                                        'order_number' => 1,
                                    ],
                                    [
                                        'question_text' => 'Describe the waste management system',
                                        'question_type' => 'textarea',
                                        'is_required' => true,
                                        'order_number' => 2,
                                    ],
                                ]
                            ],
                        ]
                    ],
                ];

            default:
                return [];
        }
    }
}
