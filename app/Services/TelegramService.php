<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    // That class is used to send messages to the Telegram chat
    // The chat ID and the bot token are stored in the services config

    public static function sendMessage(array $message): void
    {
        $chatId = config('services.telegram.chat_id');
        $botToken = config('services.telegram.bot_token');

        // Create a string from the message array
        // Text can contain special characters, such as markdown or HTML
        $messageString = '';
        foreach ($message as $key => $value) {
            $messageString .= "$key: $value\n";
        }

        // Send the message to the Telegram chat
        $url = "https://api.telegram.org/bot$botToken/sendMessage";

//        if env is local, don't send the message

        if (env('APP_ENV') == 'local') {
            Log::info('Telegram message: ' . $messageString);
            return;
        }

        $response = Http::get($url, [
            'chat_id' => $chatId,
            'text' => $messageString,
        ]);
    }
}
