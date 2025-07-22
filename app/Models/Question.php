<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'question_text',
        'response_type',
        'options',
        'order',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    const RESPONSE_TYPES = [
        'text' => 'Text Input',
        'textarea' => 'Text Area',
        'yes_no' => 'Yes/No',
        'select' => 'Select Option',
        'number' => 'Number',
        'date' => 'Date',
        'table' => 'Table/Matrix',
    ];

    /**
     * Get the section that owns the question.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the responses for the question.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Get the template through the section.
     */
    public function template()
    {
        return $this->hasOneThrough(Template::class, Section::class, 'id', 'id', 'section_id', 'template_id');
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include required questions.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query by response type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('response_type', $type);
    }

    /**
     * Parse table structure from question text
     */
    public function parseTableStructure()
    {
        $text = $this->question_text;
        
        // Look for table-like structures with | delimiter
        if (strpos($text, '|') !== false) {
            $lines = explode("\n", $text);
            $tableData = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '|') !== false) {
                    // Split by | and clean up each cell
                    $cells = array_map('trim', explode('|', $line));
                    // Remove empty cells from start and end
                    $cells = array_filter($cells, function($cell) {
                        return $cell !== '';
                    });
                    
                    if (count($cells) > 0) {
                        $tableData[] = array_values($cells);
                    }
                }
            }
            
            return $tableData;
        }
        
        // Create actual table structures based on the Excel files
        if ($this->response_type === 'table') {
            $templateName = $this->section->template->name ?? '';
            $sectionName = $this->section->name ?? '';
            
            // Stock count tables - from Excel Row 5
            if (stripos($templateName, 'stock count') !== false) {
                return [
                    ['Name of Vaccine', 'UoM', 'Batch No.', 'Expiry date', 'Quantity counted (A)', 'Quantity recorded in vLMIS (B)', 'Quantity recorded in Stock Register (C)', 'Variance (A-B)', 'Variance (A-C)', 'Variance (B-C)'],
                    ['Pentavalent vaccine', '(I Vial = 1 dose)', '', '', '', '', '', '', '', ''],
                    ['Inactivated Polio vaccine (IPV)', '(I Vial = 10 doses)', '', '', '', '', '', '', '', ''],
                    ['Pneumococcal Conjugate Vaccine (PCV)', '(I Vial = 4 doses)', '', '', '', '', '', '', '', ''],
                    ['Measles and Rubella', '(I Vial = 10 doses)', '', '', '', '', '', '', '', ''],
                    ['BCG', '(I Vial = 20 doses)', '', '', '', '', '', '', '', '']
                ];
            }
            
            // CCE (Cold Chain Equipment) tables - from Excel Row 4
            if (stripos($templateName, 'cce') !== false) {
                return [
                    ['#', 'Equipment', 'Serial Number', 'Capacity', 'Is Equipment Functional?', 'Power Source'],
                    ['1', '', '', '', 'YES/NO', 'ELECTRICAL GRID'],
                    ['2', '', '', '', 'YES/NO', 'GENERATOR'],
                    ['3', '', '', '', 'YES/NO', 'ELECTRICAL GRID & GENERATOR'],
                    ['4', '', '', '', 'YES/NO', 'SOLAR POWER']
                ];
            }
            
            // Stock dispatch reconciliation tables - from Excel Row 13
            if (stripos($templateName, 'stock dispatch') !== false) {
                // Different headers based on level
                if (stripos($templateName, 'pvs') !== false) {
                    $deliverySource = 'Central Vaccine Store Delivery';
                    $receiptSource = 'Provincial Vaccine Store Receipt';
                } elseif (stripos($templateName, 'dvs') !== false) {
                    $deliverySource = 'PVS Delivery';
                    $receiptSource = 'DVS Receipt';
                } else { // Health Facility
                    $deliverySource = 'Tehsil';
                    $receiptSource = 'Health facility';
                }
                
                return [
                    ['Vaccines & Commodities', 'UoM', 'Date', 'Batch No.', 'Quantity', 'Date', 'Batch No.', 'Quantity', 'Stock Quantity', 'Quantity'],
                    ['', '', $deliverySource, '', '', $receiptSource, '', '', 'Store', 'VARIANCE'],
                    ['A', 'Pentavalent vaccine', '(I Vial = 1 dose)', '', '', '', '', '', '', ''],
                    ['B', 'Inactivated Polio vaccine (IPV)', '(I Vial = 10 doses)', '', '', '', '', '', '', ''],
                    ['C', 'Pneumococcal Conjugate Vaccine (PCV)', '(I Vial = 4 doses)', '', '', '', '', '', '', ''],
                    ['D', 'Measles and Rubella', '(I Vial = 10 doses)', '', '', '', '', '', '', '']
                ];
            }
            
            // Stock reconciliation tables - from Excel Row 5
            if (stripos($templateName, 'stock reconciliation') !== false) {
                return [
                    ['Vaccines & Commodities', 'UoM', 'CLOSING BALANCE as of 31 Dec 2022 (A)', 'OPENING BALANCE as of 1 Jan 2023 (B)', 'TOTAL RECEIPTS (Vaccine Register/Stock Card) [Jan \'23 - Nov \'24]', 'TOTAL ISSUES (Vaccine Register/Stock Card) [Jan \'23 - Nov \'24]', 'ADJUSTMENTS (damages, expiries etc) [Jan \'23 - Nov \'24]', 'EXPECTED BALANCE (C)', 'STOCK BALANCE (D)', 'PHYSICAL COUNT BALANCE (E)', 'VAR 1 (A-B)', 'VAR 2(C-D)', 'VAR 3(D-E)', 'AMC', 'CURRENT MOS'],
                    ['Pentavalent vaccine', '(I Vial = 1 dose)', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['Inactivated Polio vaccine (IPV)', '(I Vial = 10 doses)', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['Pneumococcal Conjugate Vaccine (PCV)', '(I Vial = 4 doses)', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['Measles and Rubella', '(I Vial = 10 doses)', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['BCG', '(I Vial = 20 doses)', '', '', '', '', '', '', '', '', '', '', '', '', '']
                ];
            }
            
            // Stock out tables - from Excel Row 6-8
            if (stripos($templateName, 'stock out') !== false) {
                return [
                    ['Name of Vaccine', 'Stockout 1', 'Stockout 2', 'Stockout 3', 'Stockout 4', 'Stockout 5', 'Stockout 6', 'Stockout 1', 'Stockout 2', 'Stockout 3', 'Stockout 4', 'Stockout 5', 'Stockout 6', 'Days', 'Days', 'Days', 'Days', 'Days', 'Days'],
                    ['', 'Starting', 'Ending', 'Starting', 'Ending', 'Starting', 'Ending', 'Starting', 'Ending', 'Starting', 'Ending', 'Starting', 'Ending', '[Diff btn start & end date]', '[Diff btn start & end date]', '[Diff btn start & end date]', '[Diff btn start & end date]', '[Diff btn start & end date]', '[Diff btn start & end date]'],
                    ['', '(date when the stock balance is NIL)', '(Stock being replenished)', '(date when the stock balance is NIL)', '(Stock being replenished)', '(date when the stock balance is NIL)', '(Stock being replenished)', '(date when the stock balance is NIL)', '(Stock being replenished)', '(date when the stock balance is NIL)', '(Stock being replenished)', '(date when the stock balance is NIL)', '(Stock being replenished)', '', '', '', '', '', ''],
                    ['Example:', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', 'E.g 1-Jan-20', '', '', '', '', '', '']
                ];
            }
            
            // Expiry tables - from Excel Row 7-9
            if (stripos($templateName, 'expir') !== false) {
                return [
                    ['Name of vaccine', 'Expiry 1', 'Expiry 2', 'Expiry 3'],
                    ['', 'Date', 'Quantity (units) doses', 'Date', 'Quantity (units) doses', 'Date', 'Quantity (units) doses'],
                    ['', '[dd/Mm/yyyy]', '', '[dd/Mm/yyyy]', '', '[dd/Mm/yyyy]', '']
                ];
            }
            
            // Data reconciliation tables - from Excel Row 7-8
            if (stripos($templateName, 'data recon') !== false) {
                return [
                    ['Monthly Report on immunisation', 'EPI MIS Record', 'Vaccine doses Issued (From Stock Ledger/vLMIS)', 'Var (a-b)', 'Wastage Rate', 'Focus on Pentavalent & IPV vaccine only'],
                    ['Month', 'Total No. of immunisations (a)', 'Total No. of immunisations (b)', 'Total doses of vaccines issued (c)', '', ''],
                    ['January', '', '', '', '', ''],
                    ['February', '', '', '', '', ''],
                    ['March', '', '', '', '', ''],
                    ['April', '', '', '', '', ''],
                    ['May', '', '', '', '', ''],
                    ['June', '', '', '', '', '']
                ];
            }
            
            // Background info tables - from Excel Row 9, 19
            if (stripos($templateName, 'background') !== false) {
                if (stripos($text, 'officers met') !== false || stripos($text, 'team') !== false) {
                    return [
                        ['Name', 'Position', 'Phone number', 'E-mail'],
                        ['', '', '', ''],
                        ['', '', '', ''],
                        ['', '', '', '']
                    ];
                }
            }
            
            // Generic table structure for other cases
            return [
                ['Item', 'Description', 'Value', 'Remarks'],
                ['', '', '', ''],
                ['', '', '', '']
            ];
        }
        
        return null;
    }
}
