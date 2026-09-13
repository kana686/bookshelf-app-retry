<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanNotification extends Notification
{
    use Queueable;

    protected $readingPlan;

    protected $timing;

    /**
     * @param  mixed  $readingPlan  読書計画のモデル
     * @param  string  $timing  'three_days_before' | 'on_due_date' | 'three_days_after'
     */
    public function __construct($readingPlan, string $timing)
    {
        $this->readingPlan = $readingPlan;
        $this->timing = $timing;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースに保存されるデータ配列（Blade側で参照されるキーと一致させる）
     */
    public function toArray($notifiable): array
    {
        $body = match ($this->timing) {
            'three_days_before' => "「{$this->readingPlan->title}」の期限が3日後に迫っています。",
            'on_due_date' => "「{$this->readingPlan->title}」の期限は本日です。",
            'three_days_after' => "「{$this->readingPlan->title}」の期限から3日が経過しました。",
            default => '読書計画に関するお知らせです。',
        };

        return [
            'timing' => $this->timing,
            'title' => '読書計画の期限通知',
            'body' => $body,
        ];
    }
}
