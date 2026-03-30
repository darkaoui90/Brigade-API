<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class PlateTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_can_list_and_view_plates_but_cannot_create(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::create([
            'name' => 'Entrées',
            'description' => null,
            'color' => '#60A5FA',
            'is_active' => true,
            'user_id' => $admin->id,
        ]);

        $plate = Plate::create([
            'name' => 'Salad',
            'description' => null,
            'price' => 10,
            'image' => null,
            'is_available' => true,
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/plates', $headers)->assertOk();
        $this->getJson("/api/plates/{$plate->id}", $headers)->assertOk()->assertJsonFragment(['id' => $plate->id]);

        $this->postJson('/api/plates', [
            'name' => 'New Plate',
            'price' => 5,
            'category_id' => $category->id,
        ], $headers)->assertStatus(403);
    }

    public function test_admin_can_create_plate_with_ingredients(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $category = Category::create([
            'name' => 'Plats Principaux',
            'description' => null,
            'color' => '#A78BFA',
            'is_active' => true,
            'user_id' => $admin->id,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Chicken',
            'tags' => ['contains_meat'],
        ]);

        $create = $this->postJson('/api/plates', [
            'name' => 'Chicken Bowl',
            'description' => 'Desc',
            'price' => 12.5,
            'category_id' => $category->id,
            'ingredient_ids' => [$ingredient->id],
        ], $headers);

        $create->assertStatus(201)->assertJsonFragment(['name' => 'Chicken Bowl']);

        $plateId = $create->json('id');
        $this->assertDatabaseHas('plats', ['id' => $plateId, 'category_id' => $category->id]);
        $this->assertDatabaseHas('ingredient_plat', ['plat_id' => $plateId, 'ingredient_id' => $ingredient->id]);
    }
}
