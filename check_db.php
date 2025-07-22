<?php

require_once 'bootstrap/app.php';

use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;

echo "Review Types: " . ReviewType::count() . PHP_EOL;
echo "Templates: " . Template::count() . PHP_EOL;
echo "Sections: " . Section::count() . PHP_EOL;
echo "Questions: " . Question::count() . PHP_EOL;

echo PHP_EOL . "District Templates:" . PHP_EOL;
$district = ReviewType::where('name', 'District')->first();
if($district) {
    foreach($district->templates as $template) {
        echo "- " . $template->name . " (Sections: " . $template->sections->count() . ")" . PHP_EOL;
    }
}

echo PHP_EOL . "Health Facility Templates:" . PHP_EOL;
$hf = ReviewType::where('name', 'Health Facility')->first();
if($hf) {
    foreach($hf->templates as $template) {
        echo "- " . $template->name . " (Sections: " . $template->sections->count() . ")" . PHP_EOL;
    }
}

echo PHP_EOL . "Province Templates:" . PHP_EOL;
$province = ReviewType::where('name', 'Province')->first();
if($province) {
    foreach($province->templates as $template) {
        echo "- " . $template->name . " (Sections: " . $template->sections->count() . ")" . PHP_EOL;
    }
}
