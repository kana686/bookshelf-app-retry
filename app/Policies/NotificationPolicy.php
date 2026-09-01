<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * 該当の通知を既読にできるか（所有者チェック）
     */
    public function update(User $user, DatabaseNotification $notification): Response
    {
        return $user->id === $notification->notifiable_id
            ? Response::allow()
            : Response::deny('この通知へのアクセス権限がないか、存在しません。');
    }
}
