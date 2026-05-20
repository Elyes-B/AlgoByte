<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Models\Problem;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProblemCreationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

    return Inertia::render('problemCreation', [
        'problems' => Problem::query()
            ->where('creatorId', $userId)
            ->with('testCases')
            ->get(),
        'dashboardCounts' => [
            // Count members that are not admins. Some rows may have null/0/false for is_admin,
            // so include those cases to ensure accurate totals.
            'total_users' => Member::query()->where(function($q) {
                $q->where('is_admin', false)
                  ->orWhere('is_admin', 0)
                  ->orWhereNull('is_admin');
            })->count('*'),
            'total_admins' => Member::query()->where('is_admin', true)->count(),
            'problems_on_site' => Problem::query()->count('*'),
            'created_problems' => Problem::query()->where('creatorId', $userId)->count('*'),
            'submissions' => Submission::query()->count('*'),
        ],
    ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'description' => ['required', 'string'],
            'solution' => ['required', 'string'],
            'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
            'explanation' => ['nullable', 'string'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'test_cases' => ['required', 'array'],
            'test_cases.*.input' => ['required', 'string'],
            'test_cases.*.expected_output' => ['required', 'string'],
            'test_cases.*.is_default' => ['boolean'],
        ]);

        $problem = Problem::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'solution' => $validated['solution'],
            'explanation' => $validated['explanation'] ?? null,
            'visibility' => $validated['visibility'],
            'difficulty' => $validated['difficulty'],
            'creatorId' => auth()->id(),
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ($validated['test_cases'] as $testCaseData) {
            $problem->testCases()->create([
                'input' => $testCaseData['input'],
                'expected_output' => $testCaseData['expected_output'],
                'is_default' => $testCaseData['is_default'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
        $problem->submissions()->create([
            'userId' => auth()->id(),
            'code' => $validated['solution'],
            'language' => $validated['language'],
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $problem->sharedSolutions()->create([
            'userId' => auth()->id(),
            'problemId' => $problem->problemId,
            'submissionId' => $problem->submissions()->latest()->first()->submissionId,
            'title'=> "Creator Solution",
            'explanation'=>$validated['explanation'] ?? null,
            'code' => $validated['solution'],
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Problem created successfully.',
            'data' => [
                'id' => $problem->id,
                'title' => $problem->title,
                'description' => $problem->description,
                'solution' => $problem->solution,
                'language' => $problem->language,
                'explanation' => $problem->explanation,
                'visibility' => $problem->visibility,
                'created_at' => $problem->created_at?->toISOString(),
                'updated_at' => $problem->updated_at?->toISOString(),
            ],
        ], 201);
    }

    public function delete(Problem $problem)
    {
        // Prevent users from deleting problems they didn't create
        if ($problem->creatorId !== Auth::id()) {
            abort(403, 'You are not authorized to delete this problem.');
        }

        // Assuming you are using Laravel's SoftDeletes trait on the Problem model
        // because your schema includes a 'deleted_at' column.
        $problem->delete();

        // Redirect back to the previous page (likely the Inertia dashboard)
        // with a success flash message.
        return redirect()->back()->with('success', 'Problem deleted successfully.');
    }
}
