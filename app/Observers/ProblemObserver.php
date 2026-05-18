<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Problem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProblemObserver
{
    /**
     * Handle the Problem "created" event.
     */
    public function created(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "updated" event.
     */
    public function updated(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "deleted" event.
     */
    public function deleted(Problem $problem): void
    {
        Notification::create([
            'user_id' => Auth::id(), 
            'title' => 'problem_deleted',
            'message' => "The problem titled '{$problem->title}' has been deleted.",
            'type' => 'info',
            'link' => null, // No link needed for deletion notification
            'created_at' => now(),
        ]);
    }

    /**
     * Handle the Problem "restored" event.
     */
    public function restored(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "force deleted" event.
     */
    public function forceDeleted(Problem $problem): void
    {
        //
    }
}
