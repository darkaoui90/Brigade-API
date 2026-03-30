<?php

namespace Tests\Feature\Api;

use App\Jobs\AnalyzeRecommendation;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_enqueues_job_and_returns_processing_then_ready(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Entrées',
            'description' => null,
            'color' => '#60A5FA',
            'is_active' => true,
            'user_id' => $admin->id,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Flour',
            'tags' => ['contains_gluten'],
        ]);

        $plate = Plate::create([
            'name' => 'Bread',
            'description' => null,
            'price' => 3,
            'image' => null,
            'is_available' => true,
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);
        $plate->ingredients()->sync([$ingredient->id]);

        $user = User::factory()->create(['is_admin' => false]);
        $user->profile()->create(['dietary_tags' => ['gluten_free']]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        Queue::fake();

        $response = $this->postJson("/api/recommendations/analyze/{$plate->id}", [], $headers);
        $response->assertStatus(202)->assertJsonFragment(['status' => 'processing']);

        Queue::assertPushed(AnalyzeRecommendation::class);

        $recommendationId = $response->json('id');
        AnalyzeRecommendation::dispatchSync($recommendationId);

        $recommendation = Recommendation::findOrFail($recommendationId);
        $this->assertSame('ready', $recommendation->status);
        $this->assertNotNull($recommendation->score);

        $this->getJson("/api/recommendations/{$plate->id}", $headers)
            ->assertOk()
            ->assertJsonFragment(['status' => 'ready']);
    }
}
