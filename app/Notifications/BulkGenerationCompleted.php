<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkGenerationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $processed;
    protected $failed;
    protected $total;
    protected $errors;
    protected $historyId;

    /**
     * Create a new notification instance.
     */
    public function __construct($processed, $failed, $total, $errors = '', $historyId = null)
    {
        $this->processed = $processed;
        $this->failed = $failed;
        $this->total = $total;
        $this->errors = $errors;
        $this->historyId = $historyId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Only use database notifications to avoid email issues
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Bulk SEO Generation Completed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your bulk SEO generation task has been completed.')
            ->line("Successfully processed: {$this->processed} items")
            ->line("Failed: {$this->failed} items")
            ->line("Total: {$this->total} items");
            
        // Use the history page if we have a history ID, otherwise fall back to stats
        if ($this->historyId) {
            $message->action('View Details', url("/admin/ai-history/{$this->historyId}"));
        } else {
            $message->action('View Details', url('/admin/ai-dashboard/stats'));
        }

        if (!empty($this->errors)) {
            $message->line('Errors encountered:')
                   ->line($this->errors);
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Determine the link based on whether we have a history ID
        $link = $this->historyId 
            ? "/admin/ai-history/{$this->historyId}" 
            : "/admin/ai-dashboard/stats";
            
        return [
            'title' => 'Bulk SEO Generation Completed',
            'message' => "Successfully processed: {$this->processed}, Failed: {$this->failed}, Total: {$this->total}",
            'processed' => $this->processed,
            'failed' => $this->failed,
            'total' => $this->total,
            'errors' => $this->errors,
            'history_id' => $this->historyId,
            'link' => $link
        ];
    }
}