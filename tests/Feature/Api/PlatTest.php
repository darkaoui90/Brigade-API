<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class PlatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_plat_only_in_own_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryA = $userA->categories()->create(['name' => 'A']);
        $categoryB = $userB->categories()->create(['name' => 'B']);

        $token = $userA->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->postJson('/api/plats', [
            'name' => 'Pizza',
            'description' => 'Desc',
            'price' => 10.5,
            'category_id' => $categoryB->id,
        ], $headers)->assertStatus(422);

        $this->postJson('/api/plats', [
            'name' => 'Pizza',
            'description' => 'Desc',
            'price' => 10.5,
            'category_id' => $categoryA->id,
        ], $headers)->assertStatus(201);
    }

    public function test_user_cannot_access_other_users_plat(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $category = $owner->categories()->create(['name' => 'Owner Category']);
        $plat = $owner->plats()->create([
            'name' => 'Burger',
            'description' => null,
            'price' => 9.99,
            'category_id' => $category->id,
        ]);

        $token = $other->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson("/api/plats/{$plat->id}", $headers)->assertStatus(403);
    }

    public function test_attach_plats_moves_them_to_category(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $from = $user->categories()->create(['name' => 'From']);
        $to = $user->categories()->create(['name' => 'To']);

        $platA = $user->plats()->create([
            'name' => 'Plat A',
            'description' => null,
            'price' => 5,
            'category_id' => $from->id,
        ]);

        $platB = $user->plats()->create([
            'name' => 'Plat B',
            'description' => null,
            'price' => 6,
            'category_id' => $from->id,
        ]);

        $this->postJson("/api/categories/{$to->id}/plats", [
            'plat_ids' => [$platA->id, $platB->id],
        ], $headers)->assertOk();

        $this->assertDatabaseHas('plats', ['id' => $platA->id, 'category_id' => $to->id]);
        $this->assertDatabaseHas('plats', ['id' => $platB->id, 'category_id' => $to->id]);
    }

    public function test_attach_plats_rejects_plats_not_owned_by_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $token = $owner->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $category = $owner->categories()->create(['name' => 'Owner Category']);

        $ownedPlat = $owner->plats()->create([
            'name' => 'Owned',
            'description' => null,
            'price' => 1,
            'category_id' => $category->id,
        ]);

        $otherCategory = $other->categories()->create(['name' => 'Other Category']);
        $otherPlat = $other->plats()->create([
            'name' => 'Other',
            'description' => null,
            'price' => 2,
            'category_id' => $otherCategory->id,
        ]);

        $this->postJson("/api/categories/{$category->id}/plats", [
            'plat_ids' => [$ownedPlat->id, $otherPlat->id],
        ], $headers)->assertStatus(422);
    }
}
