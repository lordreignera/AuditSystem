<?php

namespace Tests\Feature;

use App\Models\Audit;
use App\Models\Country;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuditSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Audit Manager']);
        Role::create(['name' => 'Auditor']);
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
        ]);
        $this->user->assignRole('Super Admin');
        
        // Create test country
        $this->country = Country::create([
            'name' => 'Test Country',
            'code' => 'TC',
            'is_active' => true
        ]);
        
        // Create test review type
        $this->reviewType = ReviewType::create([
            'name' => 'Test Review Type',
            'description' => 'Test Description',
            'is_active' => true
        ]);
        
        // Create default template
        $this->template = Template::create([
            'review_type_id' => $this->reviewType->id,
            'name' => 'Test Template',
            'description' => 'Test Template Description',
            'order' => 1,
            'is_active' => true,
            'is_default' => true,
            'audit_id' => null
        ]);
    }

    public function test_audit_can_be_created()
    {
        $this->actingAs($this->user);
        
        $auditData = [
            'name' => 'Test Audit',
            'description' => 'Test audit description',
            'country_id' => $this->country->id,
            'start_date' => '2025-01-01',
            'duration_value' => 30,
            'duration_unit' => 'days'
        ];
        
        $response = $this->post(route('admin.audits.store'), $auditData);
        
        $response->assertRedirect(route('admin.audits.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('audits', [
            'name' => 'Test Audit',
            'country_id' => $this->country->id
        ]);
    }

    public function test_review_type_can_be_attached_to_audit()
    {
        $this->actingAs($this->user);
        
        $audit = Audit::create([
            'name' => 'Test Audit',
            'description' => 'Test Description',
            'country_id' => $this->country->id,
            'start_date' => '2025-01-01',
            'review_code' => 'TEST001'
        ]);
        
        $response = $this->post(route('admin.audits.attachReviewType', $audit), [
            'review_type_id' => $this->reviewType->id
        ]);
        
        $response->assertRedirect(route('admin.audits.dashboard', $audit));
        $response->assertSessionHas('success');
        
        // Check that attachment was created
        $this->assertDatabaseHas('audit_review_type_attachments', [
            'audit_id' => $audit->id,
            'review_type_id' => $this->reviewType->id,
            'duplicate_number' => 1
        ]);
        
        // Check that audit-specific template was created
        $this->assertDatabaseHas('templates', [
            'review_type_id' => $this->reviewType->id,
            'audit_id' => $audit->id,
            'is_default' => false
        ]);
    }

    public function test_review_type_can_be_detached_from_audit()
    {
        $this->actingAs($this->user);
        
        $audit = Audit::create([
            'name' => 'Test Audit',
            'description' => 'Test Description',
            'country_id' => $this->country->id,
            'start_date' => '2025-01-01',
            'review_code' => 'TEST002'
        ]);
        
        // First attach the review type
        $this->post(route('admin.audits.attachReviewType', $audit), [
            'review_type_id' => $this->reviewType->id
        ]);
        
        // Then detach it
        $response = $this->post(route('admin.audits.detachReviewType', $audit), [
            'review_type_id' => $this->reviewType->id
        ]);
        
        $response->assertRedirect(route('admin.audits.dashboard', $audit));
        $response->assertSessionHas('success');
        
        // Check that attachment was removed
        $this->assertDatabaseMissing('audit_review_type_attachments', [
            'audit_id' => $audit->id,
            'review_type_id' => $this->reviewType->id
        ]);
        
        // Check that audit-specific template was removed
        $this->assertDatabaseMissing('templates', [
            'review_type_id' => $this->reviewType->id,
            'audit_id' => $audit->id
        ]);
    }

    public function test_audit_dashboard_loads_correctly()
    {
        $this->actingAs($this->user);
        
        $audit = Audit::create([
            'name' => 'Test Audit',
            'description' => 'Test Description',
            'country_id' => $this->country->id,
            'start_date' => '2025-01-01',
            'review_code' => 'TEST003'
        ]);
        
        $response = $this->get(route('admin.audits.dashboard', $audit));
        
        $response->assertStatus(200);
        $response->assertViewHas('audit');
        $response->assertViewHas('availableReviewTypes');
        $response->assertViewHas('attachedReviewTypes');
    }

    public function test_unauthorized_user_cannot_access_audits()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser);
        
        $response = $this->get(route('admin.audits.index'));
        
        $response->assertStatus(403);
    }
}
