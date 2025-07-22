<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;

class DemoCrudData extends Command
{
    protected $signature = 'audit:demo-crud';
    protected $description = 'Show sample data for CRUD testing';

    public function handle()
    {
        $this->info('=== AUDIT CRUD DEMO DATA ===');
        
        // Show review types with templates
        $reviewTypes = ReviewType::with('templates')->get();
        
        foreach ($reviewTypes as $reviewType) {
            $this->info("\n🏥 Review Type: {$reviewType->name}");
            
            if ($reviewType->templates->isEmpty()) {
                $this->warn("   ⚠️  No templates available");
                continue;
            }
            
            foreach ($reviewType->templates as $template) {
                $this->line("   📋 Template: {$template->name}");
                $this->line("      ID: {$template->id}");
                $this->line("      Sections: {$template->sections()->count()}");
                $this->line("      Questions: " . $template->sections()->withCount('questions')->get()->sum('questions_count'));
                
                // Show URL to create audit
                $url = url("/admin/review-types-crud/{$reviewType->id}/template/{$template->id}/create-audit");
                $this->line("      🔗 Create Audit URL: {$url}");
            }
        }
        
        $this->info("\n=== SAMPLE QUESTIONS ===");
        $questions = Question::with('section.template.reviewType')->take(5)->get();
        
        foreach ($questions as $question) {
            $this->line("\n❓ Question ID: {$question->id}");
            $this->line("   Text: " . substr($question->question_text, 0, 80) . "...");
            $this->line("   Type: {$question->response_type}");
            $this->line("   Section: {$question->section->name}");
            $this->line("   Template: {$question->section->template->name}");
            $this->line("   Review Type: {$question->section->template->reviewType->name}");
        }
        
        $this->info("\n✅ Demo data ready! Visit the URLs above to test CRUD functionality.");
    }
}
