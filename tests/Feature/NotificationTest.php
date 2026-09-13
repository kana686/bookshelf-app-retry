<?php

namespace Tests\Feature;

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
        $user->notify(new ReadingPlanNotification);

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

        $userA->notify(new ReadingPlanNotification);
        $userB->notify(new ReadingPlanNotification);

        $notificationA = $userA->notifications->first();
        $notificationB = $userB->notifications->first();

        $response = $this->actingAs($userA)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee($notificationA->data['message'] ?? '通知');
        $response->assertDontSee($notificationB->id);
    }

    public function test_自分の通知を正常に既読にできる(): void
    {
        $user = User::factory()->create();
        $user->notify(new ReadingPlanNotification);
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

        $userB->notify(new ReadingPlanNotification);
        $notificationB = $userB->notifications->first();

        $response = $this->actingAs($userA)->post(route('notifications.read', $notificationB));

        $response->assertStatus(403);
        $this->assertNull($notificationB->fresh()->read_at);
    }

    public function test_未ログイン時に通知を既読にしようとするとログイン画面に遷移する(): void
    {
        $user = User::factory()->create();
        $user->notify(new ReadingPlanNotification);
        $notification = $user->notifications->first();

        $response = $this->post(route('notifications.read', $notification));

        $response->assertRedirect(route('login'));
        $this->assertNull($notification->fresh()->read_at);
    }
}
