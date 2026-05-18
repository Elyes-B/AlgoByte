<?php

namespace App\Notifications;

use App\Models\Problem;
use Illuminate\Bus\Queueable;

class ProblemStatusUpdated
{
    use Queueable;

    public $problem;

    /**
     * Create a new notification instance.
     */
    public function __construct(Problem $problem)
    {
        $this->problem = $problem;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // We only want to save this to the database for the web UI header
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * This data gets saved as JSON in the 'data' column of the database.
     */
    public function toArray(object $notifiable): array
    {
        $statusStr = strtolower($this->problem->status);

        return [
            'problem_id' => $this->problem->problemId,
            'title' => $this->problem->title,
            'status' => $this->problem->status,
            // Create a clean message we can just print directly in Vue
            'message' => "Your problem '{$this->problem->title}' has been {$statusStr}.",
            // Optional: icon info to help style the UI later
            'icon' => $this->problem->status === 'Accepted' ? 'check-circle' : 'x-circle',
        ];
    }
}
