<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
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

    public function test_読書計画作成画面が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.create');
        $response->assertViewHas('books');
    }

    public function test_未ログイン時に読書計画作成画面にアクセスするとログイン画面に遷移する()
    {
        $response = $this->get(route('reading-plans.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_読書計画を正常に登録できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $data);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を登録しました。');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ]);
    }

    public function test_書籍が未選択だった場合バリデーションエラーになり登録できない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => '',
            'target_date' => '2026-12-31',
        ]);

        $response->assertInvalid([
            'book_id' => '書籍を選択してください',
        ]);

        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_書籍が存在しなかった場合バリデーションエラーになり登録できない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => 99999,
            'target_date' => '2026-12-31',
        ]);

        $response->assertInvalid([
            'book_id' => '選択された書籍が存在しません',
        ]);
        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_目標日が未入力だった場合バリデーションエラーになり登録できない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => '',
        ]);

        $response->assertInvalid([
            'target_date' => '期日を入力してください',
        ]);
        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_目標日が有効な日付でなかった場合バリデーションエラーになり登録できない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => 'not-a-date',
        ]);

        $response->assertInvalid([
            'target_date' => '有効な日付形式で入力してください',
        ]);
        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_読書計画編集画面が表示される()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.edit');
        $response->assertViewHas('readingPlan', $readingPlan);
    }

    public function test_他人の読書計画の編集画面にはアクセスできず403エラーになる()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $otherPlan));

        $response->assertStatus(403);
    }

    public function test_未ログイン時に読書計画編集画面にアクセスするとログイン画面に遷移する()
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get(route('reading-plans.edit', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    public function test_読書計画の期日を正常に更新できる()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-05-06',
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '2026-06-01',
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました。');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-06-01',
        ]);
    }

    public function test_他人の読書計画は更新できず403エラーになる()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'target_date' => '2026-05-06',
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $otherPlan), [
            'target_date' => '2026-06-01',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reading_plans', [
            'id' => $otherPlan->id,
            'target_date' => '2026-05-06',
        ]);
    }

    public function test_更新時に期日が未入力だった場合バリデーションエラーになり更新できない()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '',
        ]);

        $response->assertInvalid([
            'target_date' => '期日を入力してください',
        ]);
    }

    public function test_更新時に期日が有効な日付でなかった場合バリデーションエラーになり更新できない()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => 'not-a-date',
        ]);

        $response->assertInvalid([
            'target_date' => '有効な日付形式で入力してください',
        ]);
    }
}
