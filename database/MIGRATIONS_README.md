# AuditSystem Database Migrations README

This document provides a comprehensive overview of all database migrations in the AuditSystem project, explaining what each migration does and how they connect to form the complete database schema.

## 📋 Table of Contents

1. [Migration Execution Order](#migration-execution-order)
2. [Core Laravel & Framework Tables](#core-laravel--framework-tables)
3. [Application Core Tables](#application-core-tables)
4. [Template & Content Management](#template--content-management)
5. [Audit System Evolution](#audit-system-evolution)
6. [Master-Duplicate Architecture](#master-duplicate-architecture)
7. [Response Isolation System](#response-isolation-system)
8. [Database Relationships](#database-relationships)
9. [Critical Indexes and Constraints](#critical-indexes-and-constraints)
10. [Migration Dependencies](#migration-dependencies)

---

## 🔄 Migration Execution Order

Migrations are executed in chronological order based on their timestamps:

### Base Framework (2014-2019)
```
2014_10_12_000000_create_users_table.php
2014_10_12_100000_create_password_reset_tokens_table.php
2014_10_12_200000_add_two_factor_columns_to_users_table.php
2019_08_19_000000_create_failed_jobs_table.php
2019_12_14_000001_create_personal_access_tokens_table.php
```

### Application Foundation (2025-07-13)
```
2025_07_13_150408_create_sessions_table.php
2025_07_13_152035_create_permission_tables.php
2025_07_13_155544_create_review_types_table.php
2025_07_13_194551_add_is_active_to_users_table.php
2025_07_13_195234_create_countries_table.php
2025_07_13_203337_create_audits_table.php
2025_07_13_213236_create_templates_table.php
2025_07_13_213236_create_sections_table.php
2025_07_13_213237_create_questions_table.php
2025_07_13_213530_create_responses_table.php
2025_07_13_214925_add_template_id_to_audits_table.php
```

### Template Architecture (2025-07-14 to 2025-07-26)
```
2025_07_14_110909_add_parent_audit_id_to_audits_table.php
2025_07_14_124314_create_audit_review_types_table.php
2025_07_14_130200_add_parent_columns_to_templates_sections_questions.php
2025_07_22_231725_add_user_id_to_audits_table.php
2025_07_26_215014_add_audit_id_to_templates_sections_questions.php
```

### Master-Duplicate System (2025-08-02 to 2025-08-03)
```
2025_08_02_000001_create_audit_attachment_system.php (empty)
2025_08_02_085008_add_facility_name_to_audit_review_type_attachments.php (empty)
2025_08_03_081216_add_master_duplicate_fields_to_audit_review_type_attachments.php
2025_08_03_084455_add_attachment_id_to_responses_table.php
2025_08_03_104023_fix_responses_unique_constraint.php
```

---

## 🏗️ Core Laravel & Framework Tables

### 1. **2014_10_12_000000_create_users_table.php**
**Purpose**: Creates the base user authentication table
**What it does**:
- Creates `users` table with basic Laravel authentication fields
- Includes Jetstream fields for teams and profile photos
- Foundation for all user management

**Key Fields**:
- `id`, `name`, `email`, `password`
- `current_team_id`, `profile_photo_path` (Jetstream)
- `email_verified_at`, `remember_token`

### 2. **2014_10_12_100000_create_password_reset_tokens_table.php**
**Purpose**: Laravel's password reset functionality
**What it does**:
- Creates `password_reset_tokens` table
- Stores temporary tokens for password reset flows

### 3. **2014_10_12_200000_add_two_factor_columns_to_users_table.php**
**Purpose**: Adds two-factor authentication support (Jetstream)
**What it does**:
- Adds `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` to users table

### 4. **2019_08_19_000000_create_failed_jobs_table.php**
**Purpose**: Laravel queue system support
**What it does**:
- Creates `failed_jobs` table for tracking failed queue jobs

### 5. **2019_12_14_000001_create_personal_access_tokens_table.php**
**Purpose**: API token management (Laravel Sanctum)
**What it does**:
- Creates `personal_access_tokens` table for API authentication

### 6. **2025_07_13_150408_create_sessions_table.php**
**Purpose**: Database-driven session storage
**What it does**:
- Creates `sessions` table for storing user sessions in database

### 7. **2025_07_13_152035_create_permission_tables.php**
**Purpose**: Role-based permission system (Spatie Laravel Permission)
**What it does**:
- Creates `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` tables
- Enables comprehensive role and permission management
- Supports team-based permissions if configured

---

## 🎯 Application Core Tables

### 8. **2025_07_13_155544_create_review_types_table.php**
**Purpose**: Categorization system for different audit types
**What it does**:
- Creates `review_types` table (e.g., "National", "Province", "District", "Health Facility")
- Each review type can have multiple templates
- Enables multi-level audit structure

**Key Fields**:
- `name` (unique) - Review type identifier
- `description` - Explanatory text
- `is_active` - Enable/disable review types

### 9. **2025_07_13_194551_add_is_active_to_users_table.php**
**Purpose**: Adds user account activation control
**What it does**:
- Adds `is_active` boolean field to users table
- Allows administrators to enable/disable user accounts

### 10. **2025_07_13_195234_create_countries_table.php**
**Purpose**: Geographic location management for audits
**What it does**:
- Creates `countries` table with ISO codes
- Links audits to specific countries
- Supports international audit deployments

**Key Fields**:
- `name`, `code` (ISO 3166-1 alpha-3), `iso_code` (alpha-2)
- `phone_code`, `currency`, `is_active`

### 11. **2025_07_13_203337_create_audits_table.php**
**Purpose**: Core audit records
**What it does**:
- Creates `audits` table - the central entity
- Links to countries, has unique review codes
- Manages audit timeline and participants

**Key Fields**:
- `name`, `description`, `review_code` (unique)
- `country_id` (foreign key to countries)
- `participants` (JSON array)
- `start_date`, `duration_value`, `duration_unit`, `end_date`

---

## 📝 Template & Content Management

### 12. **2025_07_13_213236_create_templates_table.php**
**Purpose**: Audit template structure
**What it does**:
- Creates `templates` table
- Templates belong to review types and contain sections
- Can be default (reusable) or audit-specific

**Key Fields**:
- `review_type_id` (foreign key)
- `name`, `description`
- `is_default`, `is_active`

### 13. **2025_07_13_213236_create_sections_table.php**
**Purpose**: Organizational units within templates
**What it does**:
- Creates `sections` table
- Templates are divided into logical sections
- Each section contains multiple questions

**Key Fields**:
- `template_id` (foreign key)
- `name`, `description`, `order`
- `is_active`

### 14. **2025_07_13_213237_create_questions_table.php**
**Purpose**: Individual audit questions
**What it does**:
- Creates `questions` table
- Supports multiple response types (text, table, yes/no, etc.)
- Configurable validation and options

**Key Fields**:
- `section_id` (foreign key)
- `question_text`, `response_type`
- `options` (JSON), `table_structure` (JSON)
- `order`, `is_required`, `is_active`

**Response Types**:
- `text`, `textarea`, `yes_no`, `select`, `number`, `date`, `table`

### 15. **2025_07_13_213530_create_responses_table.php**
**Purpose**: User answers to audit questions
**What it does**:
- Creates `responses` table
- Stores actual audit responses from users
- Links responses to specific audits and questions

**Key Fields**:
- `audit_id`, `question_id` (foreign keys)
- `answer` (text/JSON), `audit_note`
- `created_by` (foreign key to users)
- **Original constraint**: `unique(['audit_id', 'question_id'])` (later modified)

---

## 🔗 Audit System Evolution

### 16. **2025_07_13_214925_add_template_id_to_audits_table.php**
**Purpose**: Direct template assignment to audits (later superseded)
**What it does**:
- Adds `template_id` and `created_by` to audits table
- Early approach to template-audit relationships
- Later replaced by more sophisticated attachment system

### 17. **2025_07_14_110909_add_parent_audit_id_to_audits_table.php**
**Purpose**: Placeholder for audit hierarchy (empty implementation)
**What it does**:
- Empty migration - planned for audit parent-child relationships
- Not currently implemented

### 18. **2025_07_14_124314_create_audit_review_types_table.php**
**Purpose**: Many-to-many relationship between audits and review types
**What it does**:
- Creates `audit_review_types` table
- Allows audits to have multiple review types
- Each combination references a specific template
- **Important**: Superseded by attachment system

**Key Fields**:
- `audit_id`, `review_type_id`, `template_id`
- `unique(['audit_id', 'review_type_id'])`

### 19. **2025_07_14_130200_add_parent_columns_to_templates_sections_questions.php**
**Purpose**: Template inheritance system for audit-specific customizations
**What it does**:
- Adds `parent_template_id` to templates (references original template)
- Adds `parent_section_id` to sections
- Adds `parent_question_id` to questions
- Adds `is_default` boolean to templates
- **Critical**: Enables audit-specific copies while preserving defaults

**Architecture**:
```
Default Template (is_default=true, parent_template_id=NULL)
    └── Audit Template (is_default=false, parent_template_id=default_id, audit_id=X)
        └── Audit Section (parent_section_id=default_section_id, audit_id=X)
            └── Audit Question (parent_question_id=default_question_id, audit_id=X)
```

### 20. **2025_07_22_231725_add_user_id_to_audits_table.php**
**Purpose**: Additional user tracking for audits
**What it does**:
- Adds `user_id` to audits table
- Supplements existing `created_by` field
- Allows nullable foreign key to users

### 21. **2025_07_26_215014_add_audit_id_to_templates_sections_questions.php**
**Purpose**: Direct audit tracking in templates, sections, and questions
**What it does**:
- Adds `audit_id` to templates, sections, and questions tables
- Enables direct filtering of audit-specific vs default content
- **Critical**: Foundation for template isolation

**Impact**:
- Default templates: `audit_id = NULL`
- Audit-specific templates: `audit_id = specific_audit_id`
- Enables complete isolation between audits and defaults

---

## 🏛️ Master-Duplicate Architecture

The master-duplicate system was introduced to handle multi-location audits (e.g., different health facilities) while maintaining response isolation.

### 22. **2025_08_02_000001_create_audit_attachment_system.php**
**Purpose**: Foundation for attachment system (empty file)
**What it does**:
- Empty migration file
- Placeholder for attachment system development

### 23. **2025_08_02_085008_add_facility_name_to_audit_review_type_attachments.php**
**Purpose**: Location naming for attachments (empty file)
**What it does**:
- Empty migration file
- Intended to add facility/location naming

### 24. **2025_08_03_081216_add_master_duplicate_fields_to_audit_review_type_attachments.php**
**Purpose**: Implements master-duplicate relationship system
**What it does**:
- Adds `master_attachment_id` (self-referencing foreign key)
- Adds `duplicate_number` (1 = master, 2+ = duplicates)
- Renames `facility_name` to `location_name`
- Adds performance index

**Architecture**:
```
Master Attachment (master_attachment_id=NULL, duplicate_number=1)
    ├── Duplicate #2 (master_attachment_id=master_id, duplicate_number=2)
    ├── Duplicate #3 (master_attachment_id=master_id, duplicate_number=3)
    └── Duplicate #N (master_attachment_id=master_id, duplicate_number=N)
```

**Key Changes**:
- Self-referencing relationship via `master_attachment_id`
- `location_name` instead of `facility_name`
- Index: `arta_audit_review_master_idx`

---

## 🔒 Response Isolation System

### 25. **2025_08_03_084455_add_attachment_id_to_responses_table.php**
**Purpose**: Links responses to specific attachment instances
**What it does**:
- Adds `attachment_id` to responses table
- Foreign key to `audit_review_type_attachments`
- Enables response isolation per location/duplicate
- Adds performance index

**Critical for**:
- Separating responses between master and duplicates
- Location-specific response tracking
- Multi-facility audit support

### 26. **2025_08_03_104023_fix_responses_unique_constraint.php**
**Purpose**: Fixes response uniqueness to include attachment isolation
**What it does**:
- **Removes old constraint**: `unique(['audit_id', 'question_id'])`
- **Adds new constraint**: `unique(['audit_id', 'attachment_id', 'question_id', 'created_by'])`
- **Critical fix**: Prevents response sharing between duplicates

**Before**: One response per question per audit (shared across all locations)
**After**: One response per question per attachment per user (isolated by location)

---

## 🔄 Database Relationships

### Core Entity Relationships
```
countries (1) ←── (n) audits (1) ←── (n) audit_review_type_attachments
                                            ↓
review_types (1) ←── (n) audit_review_type_attachments (1) ←── (n) responses
                                            ↓
review_types (1) ←── (n) templates (1) ←── (n) sections (1) ←── (n) questions (1) ←── (n) responses
```

### Master-Duplicate Relationships
```
audit_review_type_attachments (master)
    ↓ master_attachment_id (self-reference)
audit_review_type_attachments (duplicates)
    ↓ attachment_id
responses (isolated per attachment)
```

### Template Inheritance
```
templates (default, audit_id=NULL)
    ↓ parent_template_id
templates (audit-specific, audit_id=X)
    ↓ template_id
sections (audit-specific, audit_id=X, parent_section_id=default_section)
    ↓ section_id
questions (audit-specific, audit_id=X, parent_question_id=default_question)
    ↓ question_id
responses (attachment_id=specific_location)
```

### User & Permission Integration
```
users (1) ←── (n) model_has_roles ──→ (n) roles (1) ←── (n) role_has_permissions ──→ (n) permissions
users (1) ←── (n) audits (created_by)
users (1) ←── (n) responses (created_by)
```

---

## 📊 Critical Indexes and Constraints

### Primary Constraints
1. **users.email**: `UNIQUE` - Email uniqueness
2. **countries.code**: `UNIQUE` - ISO country code uniqueness
3. **countries.iso_code**: `UNIQUE` - ISO 2-letter code uniqueness
4. **audits.review_code**: `UNIQUE` - Audit identifier uniqueness
5. **review_types.name**: `UNIQUE` - Review type name uniqueness

### Composite Constraints
1. **responses**: `UNIQUE(['audit_id', 'attachment_id', 'question_id', 'created_by'])` - Response isolation
2. **audit_review_types**: `UNIQUE(['audit_id', 'review_type_id'])` - One review type per audit (superseded)

### Performance Indexes
1. **arta_audit_review_master_idx**: `['audit_id', 'review_type_id', 'master_attachment_id']`
2. **resp_audit_attach_quest_idx**: `['audit_id', 'attachment_id', 'question_id']`

### Foreign Key Relationships
- All major entities properly constrained with `ON DELETE CASCADE` or `ON DELETE SET NULL`
- Self-referencing constraints for master-duplicate relationships
- Permission system constraints for role/permission management

---

## 🔧 Migration Dependencies

### Critical Dependencies
1. **Users must exist** before audits (created_by, user_id)
2. **Countries must exist** before audits (country_id)
3. **Review types must exist** before templates
4. **Templates must exist** before sections
5. **Sections must exist** before questions
6. **Audit + Questions must exist** before responses
7. **Audit review type attachments must exist** before responses (attachment_id)

### Template System Dependencies
1. Default templates must be created before audit-specific copies
2. Parent relationships require default entities to exist first
3. Audit-specific templates require audit_id to be set

### Master-Duplicate Dependencies
1. Master attachment must exist before duplicates can reference it
2. Responses require attachment_id for proper isolation
3. Unique constraints must be updated to include attachment_id

---

## 🚀 Running Migrations

### Initial Setup
```bash
# Run all migrations in order
php artisan migrate

# Seed with sample data
php artisan db:seed
```

### Development Reset
```bash
# Reset and re-run migrations
php artisan migrate:fresh --seed
```

### Production Updates
```bash
# Run only pending migrations
php artisan migrate

# Check migration status
php artisan migrate:status
```

---

## ⚠️ Important Notes

### Template Isolation
- **Never use `replicate()`** on default templates directly
- Always check `whereNull('audit_id')` when loading defaults
- Audit-specific copies have `audit_id` set and reference parents

### Response Isolation
- **Critical**: Responses are isolated by `attachment_id`
- Each location/duplicate has completely separate responses
- Master handles CRUD operations, duplicates inherit structure

### Master-Duplicate Rules
- First attachment = master (handles template management)
- Duplicates share structure but maintain independent responses
- Removing master removes all duplicates
- Duplicates cannot modify template structure

### Data Integrity
- Unique constraints prevent response sharing
- Foreign key constraints maintain referential integrity
- Cascade deletes handle cleanup automatically

---

This migration system provides a robust foundation for multi-level, multi-location audit management with complete data isolation and template inheritance.
