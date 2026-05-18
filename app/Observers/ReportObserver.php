<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportObserver
{
    /**
     * Handle the Report "created" event.
     */
    public function created(Report $report): void
    {
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'report_created',
            'message' => "A new report with the id '{$report->id}' has been created.",
            'type' => 'info',
            'link' => null,
            'created_at' => now(),
        ]);
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "restored" event.
     */
    public function restored(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "force deleted" event.
     */
    public function forceDeleted(Report $report): void
    {
        //
    }
}
