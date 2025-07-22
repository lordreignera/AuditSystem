<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;

class ShowImportedStructure extends Command
{
    protected $signature = 'audit:show-structure';
    protected $description = 'Display the imported audit structure from Excel files';

    public function handle()
    {
        $this->info('Imported Audit Structure');
        $this->line(str_repeat('=', 50));
        
        $reviewTypes = ReviewType::with(['templates.sections.questions'])->get();
        
        foreach ($reviewTypes as $reviewType) {
            $this->info("REVIEW TYPE: {$reviewType->name}");
            $this->line("Templates: " . $reviewType->templates->count());
            
            foreach ($reviewType->templates as $template) {
                $this->line("  └─ TEMPLATE: {$template->name}");
                $this->line("     Sections: " . $template->sections->count());
                
                foreach ($template->sections as $section) {
                    $this->line("     └─ SECTION: {$section->name}");
                    $this->line("        Questions: " . $section->questions->count());
                    
                    // Show first few questions as examples
                    $questions = $section->questions->take(3);
                    foreach ($questions as $question) {
                        $questionText = strlen($question->question_text) > 60 
                            ? substr($question->question_text, 0, 57) . '...'
                            : $question->question_text;
                        $this->line("        └─ Q{$question->order}: {$questionText}");
                    }
                    
                    if ($section->questions->count() > 3) {
                        $remaining = $section->questions->count() - 3;
                        $this->line("        └─ ... and {$remaining} more questions");
                    }
                }
            }
            $this->line("");
        }
        
        $this->info("SUMMARY:");
        $this->line("Review Types: " . ReviewType::count());
        $this->line("Templates: " . Template::count());
        $this->line("Sections: " . Section::count());
        $this->line("Questions: " . Question::count());
    }
}
