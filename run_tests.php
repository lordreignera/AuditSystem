#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

// Run tests and capture output (Windows path)
$output = shell_exec('vendor\bin\phpunit --stop-on-failure 2>&1');

// Write to a log file for review
file_put_contents('test_results.log', $output);

echo "Tests completed. Results saved to test_results.log\n";
echo "Last 50 lines of output:\n";
echo "========================\n";

$lines = explode("\n", $output);
$lastLines = array_slice($lines, -50);
echo implode("\n", $lastLines);
