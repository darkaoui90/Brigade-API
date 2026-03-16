<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_crud_categories(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $create = $this->postJson('/api/categories', ['name' => 'Entrées'], $headers);
        $create->assertStatus(201)->assertJsonFragment(['name' => 'Entrées']);

        $categoryId = $create->json('id');

        $this->getJson('/api/categories', $headers)
            ->assertOk()
            ->assertJsonFragment(['id' => $categoryId]);

        $this->getJson("/api/categories/{$categoryId}", $headers)
            ->assertOk()
            ->assertJsonFragment(['id' => $categoryId]);

        $this->putJson("/api/categories/{$categoryId}", ['name' => 'Desserts'], $headers)
            ->assertOk()
            ->assertJsonFragment(['name' => 'Desserts']);

        $this->deleteJson("/api/categories/{$categoryId}", [], $headers)
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_category_name_is_unique_per_user_only(): void
    {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('api-token')->plainTextToken;
        $headersA = ['Authorization' => "Bearer {$tokenA}"];

        $this->postJson('/api/categories', ['name' => 'Boissons'], $headersA)->assertStatus(201);
        $this->postJson('/api/categories', ['name' => 'Boissons'], $headersA)->assertStatus(422);

        $userB = User::factory()->create();
        $tokenB = $userB->createToken('api-token')->plainTextToken;
        $headersB = ['Authorization' => "Bearer {$tokenB}"];

        $this->postJson('/api/categories', ['name' => 'Boissons'], $headersB)->assertStatus(201);
    }

    public function test_user_cannot_access_other_users_category(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $category = $owner->categories()->create(['name' => 'Owner Category']);

        $token = $other->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson("/api/categories/{$category->id}", $headers)->assertStatus(403);
    }
}
