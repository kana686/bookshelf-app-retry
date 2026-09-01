<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class NotificationService
{
    /**
     * ログインユーザーの通知一覧を取得する
     */
    public function getUserNotifications(Authenticatable $user)
    {
        return $user->notifications()->paginate(10);
    }

    /**
     * 指定した通知を既読にする（所有者チェック含む）
     */
    public function markAsRead(Authenticatable $user, string $notificationId)
    {
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return $notification;
    }
}
