<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TelegramService
{
    /**
     * Check if Telegram notifications are configured and enabled.
     */
    public function isEnabled(): bool
    {
        $token = (string) config('services.telegram.bot_token');

        return (bool) config('services.telegram.enabled')
            && $token !== '';
    }

    /**
     * Send message to one or more Telegram chats.
     *
     * @param  array<int, string|int>|null  $chatIds
     */
    public function sendMessage(string $message, ?array $chatIds = null): void
    {
        $targets = $chatIds ?? $this->resolveChatIds();

        if (!$this->isEnabled() || empty($targets)) {
            return;
        }

        $token = (string) config('services.telegram.bot_token');

        $successCount = 0;
        $errors = [];

        foreach ($targets as $chatId) {
            $response = Http::retry(3, 300)
                ->timeout(10)
                ->asForm()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => (string) $chatId,
                    'text' => $message,
                    'disable_web_page_preview' => true,
                ]);

            if ($response->successful()) {
                $successCount++;
                continue;
            }

            $errors[] = [
                'chat_id' => (string) $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
            ];
        }

        if ($successCount === 0 && !empty($errors)) {
            throw new RuntimeException('Failed to send Telegram notification to all chat IDs.');
        }

        if (!empty($errors)) {
            Log::warning('Telegram notification partially failed.', [
                'errors' => $errors,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function resolveChatIds(): array
    {
        $chatIds = config('services.telegram.chat_ids', []);

        if (!is_array($chatIds)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($chatId): string {
            return trim((string) $chatId);
        }, $chatIds)));
    }
}
