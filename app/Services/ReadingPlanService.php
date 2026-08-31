<?php

namespace App\Services;

use App\Models\ReadingPlan;
use Illuminate\Support\Collection;

class ReadingPlanService
{
    /**
     * ログインユーザーの読書計画一覧を取得（ステータス絞り込み対応）
     */
    public function getFilteredPlans(int $userId, ?string $status): Collection
    {
        $query = ReadingPlan::where('user_id', $userId)
            ->with('book')
            ->orderBy('target_date', 'asc');

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        return $query->get();
    }

    /**
     * 新規の読書計画を登録する
     */
    public function createPlan(array $data, int $userId): ReadingPlan
    {
        return ReadingPlan::create([
            'user_id' => $userId,
            'book_id' => $data['book_id'],
            'target_date' => $data['target_date'],
            'status' => 0,
        ]);
    }

    /**
     * 読書計画を更新する
     */
    public function updatePlan(ReadingPlan $readingPlan, array $data): ReadingPlan
    {
        $readingPlan->update([
            'target_date' => $data['target_date'],
        ]);

        return $readingPlan;
    }

    /**
     * 読書計画を削除する
     */
    public function deletePlan(ReadingPlan $readingPlan): void
    {
        $readingPlan->delete();
    }
}
