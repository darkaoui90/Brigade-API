<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class IngredientTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredients_endpoints_are_admin_only(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/ingredients', $headers)->assertStatus(403);
        $this->postJson('/api/ingredients', ['name' => 'Sugar'], $headers)->assertStatus(403);
    }
}
