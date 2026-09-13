<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の通知一覧が正常に表示される(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanNotification($readingPlan, 'three_days_before'));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('notifications.index');
    }

    public function test_未ログイン時に通知一覧にアクセスするとログイン画面に遷移する(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログインユーザー以外の通知が一覧に混入しない(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $readingPlanA = ReadingPlan::factory()->create(['user_id' => $userA->id]);
        $readingPlanB = ReadingPlan::factory()->create(['user_id' => $userB->id]);

        $userA->notify(new ReadingPlanNotification($readingPlanA, 'three_days_before'));
        $userB->notify(new ReadingPlanNotification($readingPlanB, 'three_days_before'));

        $notificationA = $userA->notifications->first();
        $notificationB = $userB->notifications->first();

        $response = $this->actingAs($userA)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee($notificationA->data['title'] ?? '通知');
        $response->assertDontSee($notificationB->id);
    }

    public function test_自分の通知を正常に既読にできる(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanNotification($readingPlan, 'on_due_date'));
        $notification = $user->notifications->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        $response->assertRedirect();
        $response->assertSessionHas('status', '通知を既読にしました。');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_他人の通知は既読にできず403エラーになる(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $readingPlanB = ReadingPlan::factory()->create(['user_id' => $userB->id]);
        $userB->notify(new ReadingPlanNotification($readingPlanB, 'three_days_after'));
        $notificationB = $userB->notifications->first();

        $response = $this->actingAs($userA)->post(route('notifications.read', $notificationB));

        $response->assertStatus(403);
        $this->assertNull($notificationB->fresh()->read_at);
    }

    public function test_未ログイン時に通知を既読にしようとするとログイン画面に遷移する(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanNotification($readingPlan, 'three_days_before'));
        $notification = $user->notifications->first();

        $response = $this->post(route('notifications.read', $notification));

        $response->assertRedirect(route('login'));
        $this->assertNull($notification->fresh()->read_at);
    }
}
