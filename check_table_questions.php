<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;

echo "=== TABLE QUESTIONS ANALYSIS ===" . PHP_EOL;

// Look for questions with pipe delimiters (actual table structure)
$tableQuestions = Question::where('question_text', 'like', '%|%')->limit(5)->get();

echo "Questions with | delimiters:" . PHP_EOL;
foreach ($tableQuestions as $question) {
    echo "Question ID: " . $question->id . PHP_EOL;
    echo "Response Type: " . $question->response_type . PHP_EOL;
    echo "Section: " . $question->section->name . PHP_EOL;
    echo "Template: " . $question->section->template->name . PHP_EOL;
    echo "Full Question Text: " . $question->question_text . PHP_EOL;
    echo "Parsed Structure: " . PHP_EOL;
    $parsed = $question->parseTableStructure();
    if ($parsed) {
        print_r($parsed);
    } else {
        echo "Could not parse structure" . PHP_EOL;
    }
    echo "=========================================" . PHP_EOL;
    echo PHP_EOL;
}

echo PHP_EOL . "=== QUESTIONS MARKED AS TABLE TYPE ===" . PHP_EOL;
$tableQuestions = Question::where('response_type', 'table')->limit(3)->get();

foreach ($tableQuestions as $question) {
    echo "Question ID: " . $question->id . PHP_EOL;
    echo "Section: " . $question->section->name . PHP_EOL;
    echo "Template: " . $question->section->template->name . PHP_EOL;
    echo "Full Question Text: " . $question->question_text . PHP_EOL;
    echo "Parsed Structure: " . PHP_EOL;
    $parsed = $question->parseTableStructure();
    if ($parsed) {
        print_r($parsed);
    } else {
        echo "Could not parse structure" . PHP_EOL;
    }
    echo "=========================================" . PHP_EOL;
    echo PHP_EOL;
}
