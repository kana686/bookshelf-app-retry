<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use App\Services\ReadingPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    protected ReadingPlanService $readingPlanService;

    public function ReadingPlanController(ReadingPlanService $readingPlanService)
    {
        $this->readingPlanService = $readingPlanService;
    }

    /**
     * 一覧画面の表示
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status');
        $userId = $request->user()->id;

        $readingPlans = $this->readingPlanService->getFilteredPlans($userId, $currentStatus);

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 削除処理
     */
    public function destroy(Request $request, ReadingPlan $readingPlan): RedirectResponse
    {
        if ($readingPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->readingPlanService->deletePlan($readingPlan);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }
}
