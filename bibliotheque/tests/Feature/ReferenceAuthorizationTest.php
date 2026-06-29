<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Reference;
use App\Models\DocumentType;
use App\Models\Category;
use App\Models\Publisher;

class ReferenceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DocumentType::create(['id' => 1, 'name' => 'article', 'label' => 'Article']);
        DocumentType::create(['id' => 2, 'name' => 'livre', 'label' => 'Livre']);
        DocumentType::create(['id' => 8, 'name' => 'autre', 'label' => 'Autre']);
        Category::create(['id' => 1, 'name' => 'Test', 'slug' => 'test']);
        Publisher::create(['id' => 1, 'name' => 'Test Publisher']);

        // Crée un faux fichier PDF pour les tests de lecture
        $testDir = storage_path('app/public/documents');
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        file_put_contents($testDir . '/test.pdf', 'fake PDF content');
    }

    public function test_guest_cannot_download()
    {
        $reference = Reference::factory()->create([
            'status' => 'published',
            'allow_download' => true,
            'cover_image' => null,
            'file_path' => null,
            'document_type_id' => 1,
            'category_id' => 1,
            'publisher_id' => 1,
        ]);

        $response = $this->getJson("/api/references/{$reference->id}/download");

        $response->assertStatus(401);
    }

    public function test_active_user_can_read()
    {
        $user = User::factory()->create(['status' => 'active']);
        $reference = Reference::factory()->create([
            'status' => 'published',
            'file_path' => null,
            'cover_image' => null,
            'document_type_id' => 1,
            'category_id' => 1,
            'publisher_id' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/references/{$reference->id}/read");

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_inactive_user_cannot_read()
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $reference = Reference::factory()->create([
            'status' => 'published',
            'file_path' => 'documents/test.pdf',
            'cover_image' => null,
            'document_type_id' => 1,
            'category_id' => 1,
            'publisher_id' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/references/{$reference->id}/read");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_read()
    {
        $reference = Reference::factory()->create([
            'status' => 'published',
            'file_path' => 'documents/test.pdf',
            'cover_image' => null,
            'document_type_id' => 1,
            'category_id' => 1,
            'publisher_id' => 1,
        ]);

        $response = $this->getJson("/api/references/{$reference->id}/read");

        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_non_existent_reference_returns_json_404()
    {
        $response = $this->getJson('/api/references/99999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Ressource non trouvée.']);
    }
}
