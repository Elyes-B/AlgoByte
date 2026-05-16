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
    // displays the main page of the admin panel where he accepts/rejects the problem
    public function index(Request $request)
    {
        // check if the user is admin, if not return 403 error
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action. Admins only.');
        }

        // Fetch problems that are Pending with their creators, ordered by the most recent, and paginate the results
        $problems = Problem::with('creator')
            ->where('status', 'Pending')
            ->latest()
            ->paginate(15);

        // Render the admin problem review page with the fetched problems
        return Inertia::render('Admin/ProblemReview', [
            'problems' => $problems
        ]);
    }

    
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
