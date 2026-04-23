<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramTicketNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public Ticket $ticket)
    {
    }

    public function handle(TelegramService $telegramService): void
    {
        if (!$telegramService->isEnabled()) {
            return;
        }

        $this->ticket->loadMissing(['user', 'categoryModel']);

        $categoryName = $this->ticket->categoryModel?->name ?? '-';
        $reporterName = $this->ticket->user?->name ?? '-';
        $ticketUrl = rtrim((string) config('app.url'), '/') . '/tickets/' . $this->ticket->id;

        $message = $this->renderTicketCreatedMessage([
            '{ticket_number}' => (string) $this->ticket->ticket_number,
            '{title}' => (string) $this->ticket->title,
            '{priority}' => strtoupper((string) $this->ticket->priority),
            '{category}' => $categoryName,
            '{reporter}' => $reporterName,
            '{ticket_url}' => $ticketUrl,
        ]);

        $recipientChatIds = User::query()
            ->whereIn('role', ['admin', 'teknisi'])
            ->where('is_active', true)
            ->where('telegram_notifications_enabled', true)
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->pluck('telegram_chat_id')
            ->all();

        if (empty($recipientChatIds)) {
            $recipientChatIds = (array) config('services.telegram.chat_ids', []);
        }

        $recipientChatIds = array_values(array_unique(array_filter(array_map(static function ($chatId): string {
            return trim((string) $chatId);
        }, $recipientChatIds))));

        $telegramService->sendMessage($message, $recipientChatIds);
    }

    /**
     * @param  array<string, string>  $placeholders
     */
    private function renderTicketCreatedMessage(array $placeholders): string
    {
        $template = (string) config('helpdesk_notifications.telegram.ticket_created_template');
        $template = str_replace('\\n', "\n", $template);

        return strtr($template, $placeholders);
    }
}
