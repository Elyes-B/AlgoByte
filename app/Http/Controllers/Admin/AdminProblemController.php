<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Problem;
use App\Notifications\ProblemStatusUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminProblemController extends Controller
{
    /**
     * Display a list of pending problems for admin review.
     */
    public function index(Request $request)
    {
        // Simple authorization check based on your migration
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action. Admins only.');
        }

        // Fetch problems that are Pending, eager loading the creator
        $problems = Problem::with('creator')
            ->where('status', 'Pending')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/ProblemReview', [
            'problems' => $problems
        ]);
    }

    /**
     * Update the status of a specific problem.
     */
    public function updateStatus(Request $request, Problem $problem)
    {
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:Accepted,Rejected'
        ]);

        $problem->status = $request->status;
$problem->save();

Notification::create([
    'user_id' => $problem->creatorId,
    'title'   => "Problem " . $request->status,
    'message' => "Your problem '{$problem->title}' has been {$request->status}.",
    'type'    => $request->status === 'Accepted' ? 'success' : 'error',
    'link'    => "/problems/{$problem->problemId}",
]);


        return back();
    }
}
