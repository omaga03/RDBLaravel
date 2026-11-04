<?php

namespace App\Notifications\Channels;

use App\Services\TelegramNotificationService;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    /**
     * Send the given notification via Telegram.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $chatId = method_exists($notifiable, 'routeNotificationForTelegram')
            ? $notifiable->routeNotificationForTelegram()
            : ($notifiable->telegram_chat_id ?? null);

        if (empty($chatId)) {
            return;
        }

        if (method_exists($notification, 'toTelegram')) {
            $message = $notification->toTelegram($notifiable);
            if (!empty($message)) {
                TelegramNotificationService::sendMessage($chatId, (string) $message, 'HTML');
            }
        }
    }
}
