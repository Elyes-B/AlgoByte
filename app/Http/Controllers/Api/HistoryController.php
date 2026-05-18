<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    /**
     * Get problems created by the user.
     */
    public function createdProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        $problems = $member->problems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        // Transform the collection inline using the paginator's through() method
        $problems->through(fn ($problem) => [
            'id' => $problem->problemId,
            'title' => $problem->title,
            'difficulty' => ucfirst((string) $problem->difficulty),
            'status' => $problem->status,
            'created_at' => optional($problem->created_at)->format('Y-m-d'),
            'creator' => [
                'username' => $problem->creator?->username ?? 'Unknown',
            ],
        ]);

        return response()->json($problems);
    }

    /**
     * Get problems successfully solved by the user.
     */
    public function solvedProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        $problems = $member->solvedProblems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        $problems->through(fn ($problem) => [
            'id' => $problem->problemId,
            'title' => $problem->title,
            'difficulty' => ucfirst((string) $problem->difficulty),
            'status' => $problem->status,
            'created_at' => optional($problem->created_at)->format('Y-m-d'),
            'creator' => [
                'username' => $problem->creator?->username ?? 'Unknown',
            ],
        ]);

        return response()->json($problems);
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

        $problems->through(fn ($problem) => [
            'id' => $problem->problemId,
            'title' => $problem->title,
            'difficulty' => ucfirst((string) $problem->difficulty),
            'status' => $problem->status,
            'created_at' => optional($problem->created_at)->format('Y-m-d'),
            'creator' => [
                'username' => $problem->creator?->username ?? 'Unknown',
            ],
        ]);

        return response()->json($problems);
    }

    /**
     * Render the Inertia history page.
     */
    public function renderHistoryPage($username)
    {
        $member = Member::where('username', $username)->firstOrFail();
        $userId = $member->userId;

        return Inertia::render('Profile/History', [
            'userId' => $userId,
        ]);
    }
}
