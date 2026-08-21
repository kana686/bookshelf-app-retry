<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReadingPlanPolicy
{
    public function delete(User $user, ReadingPlan $readingPlan): Response
    {
        return $user->id === $readingPlan->user_id
            ? Response::allow()
            : Response::deny('自身の作成した読書計画のみ削除できます');
    }
}
