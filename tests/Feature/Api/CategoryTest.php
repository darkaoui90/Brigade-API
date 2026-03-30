<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_categories_but_cannot_create(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/categories', $headers)->assertOk();

        $this->postJson('/api/categories', ['name' => 'Entrées'], $headers)->assertStatus(403);
    }

    public function test_admin_can_crud_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $create = $this->postJson('/api/categories', [
            'name' => 'Desserts',
            'description' => 'Sweet finishes',
            'color' => '#FB7185',
            'is_active' => true,
        ], $headers);

        $create->assertStatus(201)->assertJsonFragment(['name' => 'Desserts']);
        $categoryId = $create->json('id');

        $this->putJson("/api/categories/{$categoryId}", [
            'name' => 'Desserts Updated',
            'color' => '#F97316',
            'is_active' => true,
        ], $headers)->assertOk()->assertJsonFragment(['name' => 'Desserts Updated']);

        $this->deleteJson("/api/categories/{$categoryId}", [], $headers)->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_non_admin_can_view_active_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Entrées',
            'description' => null,
            'color' => '#60A5FA',
            'is_active' => true,
            'user_id' => $admin->id,
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson("/api/categories/{$category->id}", $headers)
            ->assertOk()
            ->assertJsonFragment(['id' => $category->id]);
    }
}
