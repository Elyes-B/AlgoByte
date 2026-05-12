<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function destroy(Notification $notification)
    {
        // Security: Ensure the user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        // Redirect back to the same page.
        // Inertia will automatically update the shared 'auth.notifications' prop.
        return back();
    }
}
