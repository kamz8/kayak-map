<?php

namespace Tests\Unit\Services\Dashboard;

use App\Models\Link;
use App\Models\Trail;
use App\Models\Section;
use App\Services\Dashboard\LinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private LinkService $linkService;
    private Trail $trail;
    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->linkService = app(LinkService::class);

        // Create test trail and section
        $this->trail = Trail::factory()->create();
        $this->section = Section::factory()->create([
            'trail_id' => $this->trail->id
        ]);
    }

    /** @test */
    public function test_can_get_links_for_trail()
    {
        // Arrange
        $link1 = Link::factory()->create();
        $link2 = Link::factory()->create();

        $this->trail->links()->attach([$link1->id, $link2->id]);

        // Act
        $links = $this->linkService->getLinksForModel($this->trail);

        // Assert
        $this->assertCount(2, $links);
        $this->assertTrue($links->contains($link1));
        $this->assertTrue($links->contains($link2));
    }

    /** @test */
    public function test_can_get_links_for_section()
    {
        // Arrange
        $link1 = Link::factory()->youtube()->create();
        $link2 = Link::factory()->facebook()->create();

        $this->section->links()->attach([$link1->id, $link2->id]);

        // Act
        $links = $this->linkService->getLinksForModel($this->section);

        // Assert
        $this->assertCount(2, $links);
        $this->assertTrue($links->contains($link1));
        $this->assertTrue($links->contains($link2));
    }

    /** @test */
    public function test_can_get_links_with_model_for_trail()
    {
        // Arrange
        $link = Link::factory()->create();
        $this->trail->links()->attach($link->id);

        // Act
        $result = $this->linkService->getLinksWithModel('trail', $this->trail->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('links', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertCount(1, $result['links']);
        $this->assertEquals($this->trail->id, $result['model']->id);
    }

    /** @test */
    public function test_can_get_links_with_model_for_section()
    {
        // Arrange
        $link = Link::factory()->wikipedia()->create();
        $this->section->links()->attach($link->id);

        // Act
        $result = $this->linkService->getLinksWithModel('section', $this->section->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('links', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertCount(1, $result['links']);
        $this->assertEquals($this->section->id, $result['model']->id);
    }

    /** @test */
    public function test_can_create_link_for_trail()
    {
        // Arrange
        $data = [
            'url' => 'https://example.com/test',
            'meta_data' => json_encode([
                'title' => 'Test Link',
                'description' => 'Test Description',
                'icon' => 'mdi-web'
            ])
        ];

        // Act
        $link = $this->linkService->createLink($this->trail, $data);

        // Assert
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('https://example.com/test', $link->url);
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'url' => 'https://example.com/test'
        ]);
        $this->assertDatabaseHas('linkables', [
            'link_id' => $link->id,
            'linkable_type' => Trail::class,
            'linkable_id' => $this->trail->id
        ]);
    }

    /** @test */
    public function test_can_create_link_for_section()
    {
        // Arrange
        $data = [
            'url' => 'https://youtube.com/watch?v=test123',
            'meta_data' => json_encode([
                'title' => 'YouTube Video',
                'description' => 'Tutorial',
                'icon' => 'mdi-youtube'
            ])
        ];

        // Act
        $link = $this->linkService->createLink($this->section, $data);

        // Assert
        $this->assertInstanceOf(Link::class, $link);
        $this->assertDatabaseHas('linkables', [
            'link_id' => $link->id,
            'linkable_type' => Section::class,
            'linkable_id' => $this->section->id
        ]);
    }

    /** @test */
    public function test_create_link_with_default_meta_data_if_not_provided()
    {
        // Arrange
        $data = [
            'url' => 'https://example.com'
        ];

        // Act
        $link = $this->linkService->createLink($this->trail, $data);

        // Assert
        $metaData = json_decode($link->meta_data, true);
        $this->assertArrayHasKey('title', $metaData);
        $this->assertArrayHasKey('description', $metaData);
        $this->assertArrayHasKey('icon', $metaData);
    }

    /** @test */
    public function test_can_update_link()
    {
        // Arrange
        $link = Link::factory()->create([
            'url' => 'https://old-url.com',
            'meta_data' => json_encode(['title' => 'Old Title', 'description' => '', 'icon' => ''])
        ]);

        $data = [
            'url' => 'https://new-url.com',
            'meta_data' => json_encode(['title' => 'New Title', 'description' => 'New Description', 'icon' => 'mdi-web'])
        ];

        // Act
        $updatedLink = $this->linkService->updateLink($link, $data);

        // Assert
        $this->assertEquals('https://new-url.com', $updatedLink->url);
        $metaData = json_decode($updatedLink->meta_data, true);
        $this->assertEquals('New Title', $metaData['title']);
        $this->assertEquals('New Description', $metaData['description']);
    }

    /** @test */
    public function test_update_link_preserves_existing_data_if_not_provided()
    {
        // Arrange
        $link = Link::factory()->create([
            'url' => 'https://original-url.com',
            'meta_data' => json_encode(['title' => 'Original Title', 'description' => '', 'icon' => ''])
        ]);

        $data = [
            'meta_data' => json_encode(['title' => 'Updated Title', 'description' => '', 'icon' => ''])
        ];

        // Act
        $updatedLink = $this->linkService->updateLink($link, $data);

        // Assert
        $this->assertEquals('https://original-url.com', $updatedLink->url); // URL unchanged
        $metaData = json_decode($updatedLink->meta_data, true);
        $this->assertEquals('Updated Title', $metaData['title']); // Title updated
    }

    /** @test */
    public function test_can_delete_link()
    {
        // Arrange
        $link = Link::factory()->create();
        $this->trail->links()->attach($link->id);

        $linkId = $link->id;

        // Act
        $result = $this->linkService->deleteLink($link);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('links', ['id' => $linkId]);
        $this->assertDatabaseMissing('linkables', ['link_id' => $linkId]);
    }

    /** @test */
    public function test_link_belongs_to_model_returns_true_for_correct_association()
    {
        // Arrange
        $link = Link::factory()->create();
        $this->trail->links()->attach($link->id);

        // Act
        $result = $this->linkService->linkBelongsToModel($link, $this->trail);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function test_link_belongs_to_model_returns_false_for_incorrect_association()
    {
        // Arrange
        $link = Link::factory()->create();
        $this->trail->links()->attach($link->id);

        $anotherTrail = Trail::factory()->create();

        // Act
        $result = $this->linkService->linkBelongsToModel($link, $anotherTrail);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function test_resolve_model_returns_trail_instance()
    {
        // Act
        $reflection = new \ReflectionClass($this->linkService);
        $method = $reflection->getMethod('resolveModel');
        $method->setAccessible(true);

        $model = $method->invoke($this->linkService, 'trail', $this->trail->id);

        // Assert
        $this->assertInstanceOf(Trail::class, $model);
        $this->assertEquals($this->trail->id, $model->id);
    }

    /** @test */
    public function test_resolve_model_returns_section_instance()
    {
        // Act & Assert
        $reflection = new \ReflectionClass($this->linkService);
        $method = $reflection->getMethod('resolveModel');
        $method->setAccessible(true);

        $model = $method->invoke($this->linkService, 'section', $this->section->id);

        $this->assertInstanceOf(Section::class, $model);
        $this->assertEquals($this->section->id, $model->id);
    }

    /** @test */
    public function test_resolve_model_throws_exception_for_invalid_type()
    {
        // Arrange
        $reflection = new \ReflectionClass($this->linkService);
        $method = $reflection->getMethod('resolveModel');
        $method->setAccessible(true);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model type: invalid');

        $method->invoke($this->linkService, 'invalid', 1);
    }

    /** @test */
    public function test_link_can_be_attached_to_multiple_models()
    {
        // Arrange
        $link = Link::factory()->create();
        $trail1 = Trail::factory()->create();
        $trail2 = Trail::factory()->create();

        // Act
        $trail1->links()->attach($link->id);
        $trail2->links()->attach($link->id);

        // Assert
        $this->assertTrue($this->linkService->linkBelongsToModel($link, $trail1));
        $this->assertTrue($this->linkService->linkBelongsToModel($link, $trail2));
        $this->assertDatabaseCount('linkables', 2);
    }
}
