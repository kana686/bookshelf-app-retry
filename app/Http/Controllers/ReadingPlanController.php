<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingPlanRequest;
use App\Http\Requests\ReadingPlanUpdateRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Services\ReadingPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    protected ReadingPlanService $readingPlanService;

    public function __construct(ReadingPlanService $readingPlanService)
    {
        $this->readingPlanService = $readingPlanService;
    }

    /**
     * 一覧画面の表示
     */
    public function index(Request $request): View
    {
        $inputStatus = $request->input('status');
        $currentStatus = ($inputStatus !== null && $inputStatus !== '') ? (int) $inputStatus : null;
        $userId = $request->user()->id;

        $readingPlans = $this->readingPlanService->getFilteredPlans($userId, $currentStatus);

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 新規作成画面の表示
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 登録処理
     */
    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        $this->readingPlanService->createPlan($request->validated(), $request->user()->id);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    /**
     * 編集画面の表示
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 更新処理
     */
    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $this->readingPlanService->updatePlan($readingPlan, $request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    /**
     * 削除処理
     */
    public function destroy(Request $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $this->readingPlanService->deletePlan($readingPlan);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }
}
