<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;

echo "=== TESTING UPDATED TABLE STRUCTURES ===" . PHP_EOL;

$tableQuestions = Question::where('response_type', 'table')->take(3)->get();

foreach ($tableQuestions as $question) {
    echo "Question ID: " . $question->id . PHP_EOL;
    echo "Template: " . $question->section->template->name . PHP_EOL;
    echo "Section: " . $question->section->name . PHP_EOL;
    echo "Question: " . substr($question->question_text, 0, 100) . "..." . PHP_EOL;
    echo "Parsed Structure:" . PHP_EOL;
    
    $parsed = $question->parseTableStructure();
    if ($parsed) {
        echo "Headers: " . implode(' | ', $parsed[0]) . PHP_EOL;
        echo "Total columns: " . count($parsed[0]) . PHP_EOL;
        echo "Total rows: " . count($parsed) . PHP_EOL;
    } else {
        echo "No structure parsed" . PHP_EOL;
    }
    
    echo str_repeat("-", 50) . PHP_EOL;
}
