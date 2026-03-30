<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class AdminStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stats_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/admin/stats', $headers)->assertStatus(403);

        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('api-token')->plainTextToken;
        $adminHeaders = ['Authorization' => "Bearer {$adminToken}"];

        $this->getJson('/api/admin/stats', $adminHeaders)->assertOk()->assertJsonStructure([
            'categories',
            'plates',
            'ingredients',
            'recommendations',
        ]);
    }
}
