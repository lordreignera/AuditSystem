# AuditSystem

A Laravel-based audit management system for healthcare facility assessments with advanced template management and review type attachments.

## 🏗️ Architecture Overview

The AuditSystem is built using Laravel framework with a sophisticated template and review type management system designed to prevent data duplication and ensure scalability.

## 🔧 Core Components

### Models
- **Audit**: Main audit records with country and user associations
- **ReviewType**: Categorization of different audit types (e.g., Health Facility, Financial Management)
- **Template**: Reusable audit templates with sections and questions
- **Section**: Organizational units within templates
- **Question**: Individual audit questions with various response types
- **Response**: User responses to audit questions

### Key Controllers
- `AuditController`: Basic CRUD operations for audits
- `AuditManagement\AuditController`: Advanced audit management with template attachment system

## 🚀 Review Type Attachment System (Anti-Duplication Architecture)

### The Problem We Solved
Previously, the system used Laravel's `replicate()` method to copy templates when attaching review types to audits. This caused:
- ❌ Exponential duplication (8 → 64 → 288 templates)
- ❌ Contaminated "default" templates with audit-specific copies
- ❌ Database bloat and performance issues
- ❌ Inconsistent data integrity

### The Solution: Attachment-Based Architecture

#### 🎯 Core Principle
**Never copy templates. Always reference defaults + track customizations separately.**

#### 📊 Database Schema
```sql
-- New attachment tracking table
audit_review_type_attachments:
- audit_id (references audits.id)
- review_type_id (references review_types.id)
- created_at, updated_at
- UNIQUE KEY (audit_id, review_type_id) -- Prevents duplicates

-- Customization tracking tables
audit_template_customizations:
- audit_id
- default_template_id (references original template)
- custom_name, custom_description, is_hidden
- created_at, updated_at

audit_question_customizations:
- audit_id  
- default_question_id (references original question)
- custom_text, custom_options, is_required, is_hidden
- created_at, updated_at
```

#### 🔄 How It Works

**1. Attaching a Review Type:**
```php
// OLD WAY (caused duplication):
$template->replicate()->save(); // Created copies with audit_id=NULL

// NEW WAY (no duplication):
AuditReviewTypeAttachment::create([
    'audit_id' => $audit->id,
    'review_type_id' => $reviewTypeId
]);
// No templates copied - just creates a simple attachment record
```

**2. Loading Templates for Dashboard:**
```php
// Get attached review types
$attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)->get();

foreach ($attachments as $attachment) {
    // Load DEFAULT templates (never copied)
    $defaultTemplates = Template::where('review_type_id', $attachment->review_type_id)
        ->whereNull('audit_id') // Key: originals only
        ->with('sections.questions')
        ->get();
        
    // Overlay any customizations
    foreach ($defaultTemplates as $template) {
        $customization = AuditTemplateCustomization::where('audit_id', $audit->id)
            ->where('default_template_id', $template->id)
            ->first();
            
        $template->effective_name = $customization 
            ? $customization->custom_name 
            : $template->name;
    }
}
```

**3. Detaching a Review Type:**
```php
// Simply delete the attachment record
AuditReviewTypeAttachment::where('audit_id', $audit->id)
    ->where('review_type_id', $reviewTypeId)
    ->delete();
    
// Clean up any customizations (optional)
AuditTemplateCustomization::where('audit_id', $audit->id)
    ->whereHas('defaultTemplate', function($q) use ($reviewTypeId) {
        $q->where('review_type_id', $reviewTypeId);
    })
    ->delete();
    
// Default templates remain completely untouched
```

#### ✨ Benefits of New Architecture

| Aspect | Old System | New System |
|--------|------------|------------|
| **Template Count** | 8 → 64 → 288 (exponential) | Always 8 (constant) |
| **Attach Operation** | Copy all templates/sections/questions | Create 1 attachment record |
| **Detach Operation** | Delete copied templates | Delete 1 attachment record |
| **Database Growth** | Exponential with each attachment | Linear growth only |
| **Default Template Integrity** | Contaminated with copies | Always pristine |
| **Performance** | Degrades with attachments | Consistent performance |
| **Data Consistency** | Prone to inconsistencies | Always consistent |

#### 🛡️ Safeguards Against Duplication

1. **Database Constraints:**
   ```sql
   UNIQUE KEY audit_review_type_attachments_audit_id_review_type_id_unique 
   (audit_id, review_type_id)
   ```

2. **Application Logic:**
   ```php
   // Check for existing attachment before creating
   $existing = AuditReviewTypeAttachment::where('audit_id', $auditId)
       ->where('review_type_id', $reviewTypeId)
       ->first();
       
   if ($existing) {
       return redirect()->back()->with('error', 'Already attached!');
   }
   ```

3. **Template Selection Logic:**
   ```php
   // Only load original templates (never copies)
   ->whereNull('audit_id') // This is the key safeguard
   ```

## 🔧 Installation & Setup

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Configure environment: `cp .env.example .env`
4. Generate key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Seed data: `php artisan db:seed`

## 🧪 Testing the Attachment System

```php
// Test script to verify no duplication
$initialCount = Template::where('review_type_id', 4)->whereNull('audit_id')->count();

// Attach review type multiple times
for ($i = 0; $i < 5; $i++) {
    // Detach if attached
    AuditReviewTypeAttachment::where('audit_id', 1)
        ->where('review_type_id', 4)->delete();
        
    // Attach
    AuditReviewTypeAttachment::create([
        'audit_id' => 1, 
        'review_type_id' => 4
    ]);
    
    $currentCount = Template::where('review_type_id', 4)->whereNull('audit_id')->count();
    echo "Cycle {$i}: {$currentCount} templates\n";
}

// Result: Always shows same count (e.g., "8 templates" for each cycle)
```

## 📝 Contributing

When working with the attachment system:
1. Never use `replicate()` for templates
2. Always check `whereNull('audit_id')` when loading defaults
3. Use attachment tables for tracking relationships
4. Use customization tables for audit-specific modifications

## 🔒 License

This project is licensed under the MIT License.
