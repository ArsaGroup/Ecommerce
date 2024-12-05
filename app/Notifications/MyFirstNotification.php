<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyFirstNotification extends Notification
{
    use Queueable;

    private $details;

    /**
     * ایجاد یک نمونه جدید از نوتیفیکیشن
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * تعیین کانال‌های ارسال نوتیفیکیشن
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // ارسال از طریق ایمیل
    }

    /**
     * تعیین نحوه نمایش نوتیفیکیشن در ایمیل
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting($this->details['greeting'])
            ->line($this->details['firstline'])
            ->line($this->details['body'])
            ->action($this->details['button'], $this->details['url'])
            ->line($this->details['lastline']);
    }

    /**
     * دریافت آرایه‌ای از نوتیفیکیشن
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'greeting' => $this->details['greeting'],
            'body' => $this->details['body'],
        ];
    }
}
