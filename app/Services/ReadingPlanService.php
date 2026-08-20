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

        if (! empty($status)) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * 読書計画を削除する
     */
    public function deletePlan(ReadingPlan $readingPlan): void
    {
        $readingPlan->delete();
    }
}
