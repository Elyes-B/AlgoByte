<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HistoryProblemResource;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HistoryController extends Controller
{
    /**
     * Get problems created by the user.
     */
    public function createdProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        // Use the relationship defined in Member.php
        $problems = $member->problems()
            ->latest()
            ->paginate(15);

        return response()->json($problems);
    }

    /**
     * Get problems successfully solved by the user.
     */
public function solvedProblems(Request $request, $userId)
{
    $member = Member::findOrFail($userId);

    $problems = $member->solvedProblems()
        ->with('creator')
        ->latest()
        ->paginate(15);

    // This wraps the paginated collection in our new Resource
    return HistoryProblemResource::collection($problems);
}

    /**
     * Get problems the user attempted but hasn't solved yet.
     */
    public function attemptedProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        $problems = $member->attemptedProblems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        return response()->json($problems);
    }

public function renderHistoryPage(Request $request, Member $username)
{
    $targetMember = $username;

    // Determine which tab we are on (default to 'created')
    $tab = $request->query('tab', 'created');

    if ($tab === 'solved') {
        $problems = $targetMember->solvedProblems()->paginate(10);
    } else {
        // Show accepted problems created by this user
        $problems = $targetMember->problems()
            ->where('status', 'Accepted')
            ->latest()
            ->paginate(10);
    }

    return Inertia::render('Profile/History', [
        'targetMember' => $targetMember,
        'problems' => $problems, // This now contains the data and meta (pagination)
        'currentTab' => $tab,
        'isOwner' => $request->user() && $request->user()->userId === $targetMember->userId,
    ]);
}
}
