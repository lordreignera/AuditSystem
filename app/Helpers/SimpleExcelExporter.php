<?php

namespace App\Helpers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SimpleExcelExporter
{
    public static function downloadCSV(Collection $data, array $headers, string $filename)
    {
        $csvData = [];
        
        // Add headers
        $csvData[] = $headers;
        
        // Add data rows
        foreach ($data as $row) {
            $csvData[] = array_values($row);
        }
        
        // Create CSV content
        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                // Escape commas and quotes in CSV
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        // Return CSV download response
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
