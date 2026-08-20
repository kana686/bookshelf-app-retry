<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーが読書計画一覧を表示できステータスで絞り込める()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan1 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 0,
        ]);
        $plan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 1,
        ]);
        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => 0]));

        $response->assertStatus(200);

        $response->assertViewHas('readingPlans', function ($plans) use ($plan1, $plan2, $otherPlan) {
            return $plans->contains($plan1)
                && ! $plans->contains($plan2)
                && ! $plans->contains($otherPlan);
        });
    }

    public function test_自分の読書計画を削除できる()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました。');
        $this->assertDatabaseMissing('reading_plans', ['id' => $readingPlan->id]);
    }

    public function test_他人の読書計画は削除できず403エラーになる()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $otherPlan));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reading_plans', ['id' => $otherPlan->id]);
    }
}
