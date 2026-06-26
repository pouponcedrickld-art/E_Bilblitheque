<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\DepositRequest;
use App\Models\DocumentType;
use App\Models\Notification;

class DepositRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DocumentType::create(['id' => 1, 'name' => 'article', 'label' => 'Article']);
        DocumentType::create(['id' => 2, 'name' => 'livre', 'label' => 'Livre']);
        \App\Models\Category::create(['id' => 1, 'name' => 'Test', 'slug' => 'test']);
        \App\Models\Publisher::create(['id' => 1, 'name' => 'Test Publisher']);
    }

    public function test_user_can_create_deposit_request()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'responsable_demande', 'status' => 'active']);

        $this->actingAs($user);

        $response = $this->postJson('/api/deposit-requests', [
            'title' => 'Test Document',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('Test Document', $response->json('title'));
    }

    public function test_user_cannot_approve_deposit_request()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'responsable_demande', 'status' => 'active']);

        $deposit = DepositRequest::factory()->create([
            'applicant_id' => $user->id,
            'assigned_manager_id' => $manager->id,
            'status' => 'pending',
            'document_type_id' => 1,
            'cover_image' => null,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/deposit-requests/{$deposit->id}/approve-manager", [
            'justification' => 'valid approval',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_approve_deposit_request()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'responsable_demande', 'status' => 'active']);

        $deposit = DepositRequest::factory()->create([
            'applicant_id' => $user->id,
            'assigned_manager_id' => $manager->id,
            'status' => 'pending',
            'document_type_id' => 1,
            'cover_image' => null,
        ]);

        $this->actingAs($manager);

        $response = $this->postJson("/api/deposit-requests/{$deposit->id}/approve-manager", [
            'justification' => 'Looks good, approved.',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved_by_manager', $deposit->fresh()->status);
    }

    public function test_admin_can_publish_approved_request()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $deposit = DepositRequest::factory()->create([
            'applicant_id' => $user->id,
            'status' => 'approved_by_manager',
            'category_id' => 1,
            'publisher_id' => 1,
            'document_type_id' => 1,
            'cover_image' => null,
            'proposed_file' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/deposit-requests/{$deposit->id}/publish");

        $response->assertStatus(200);
        $this->assertEquals('published', $deposit->fresh()->status);
    }

    public function test_applicant_notified_on_manager_rejection()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'responsable_demande', 'status' => 'active']);

        $deposit = DepositRequest::factory()->create([
            'applicant_id' => $user->id,
            'assigned_manager_id' => $manager->id,
            'status' => 'pending',
            'document_type_id' => 1,
            'cover_image' => null,
        ]);

        $this->actingAs($manager);

        $this->postJson("/api/deposit-requests/{$deposit->id}/reject-manager", [
            'justification' => 'Document non conforme aux exigences de publication.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Votre demande a été refusée',
        ]);
    }

    public function test_manager_cannot_publish()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'responsable_demande', 'status' => 'active']);

        $deposit = DepositRequest::factory()->create([
            'applicant_id' => $user->id,
            'assigned_manager_id' => $manager->id,
            'status' => 'approved_by_manager',
            'document_type_id' => 1,
            'cover_image' => null,
        ]);

        $this->actingAs($manager);

        $response = $this->postJson("/api/deposit-requests/{$deposit->id}/publish");

        $response->assertStatus(403);
    }
}
