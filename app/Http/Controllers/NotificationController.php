<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = $this->notificationService->getUserNotifications(Auth::user());

        return view('notifications.index', compact('notifications'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $this->notificationService->markAsRead(Auth::user(), $id);

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('status', '通知を既読にしました。');
        } catch (ModelNotFoundException $e) {
            abort(403, 'この通知へのアクセス権限がないか、存在しません。');
        }
    }
}
