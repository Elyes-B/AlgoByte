<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;
use Inertia\Inertia;
use App\Models\Member;
class ProblemBrowsingController extends Controller
{

    public function makePublic(Request $request, Problem $problem)
    {

        $problem->visibility = 'public';
        $problem->save();

        return response()->json(['message' => 'Problem is now public']);
    }
    public function makePrivate(Request $request, Problem $problem)
    {

        $problem->visibility = 'private';
        $problem->save();

        return response()->json(['message' => 'Problem is now private']);
    }


    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $difficulty = strtolower(trim((string) $request->input('difficulty', 'all')));
        $allowedDifficulties = ['all', 'easy', 'medium', 'hard'];

        if (!in_array($difficulty, $allowedDifficulties, true)) {
            $difficulty = 'all';
        }

        $problems = Problem::query()
            ->with('creator:userId,username')
            ->where(function ($query) {
                $query->where('visibility', 'public')
                      ->orWhere(function ($query) {
                          $query->where('visibility', 'private')
                                ->where('creatorId', auth()->id());
                      });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($difficulty !== 'all', function ($query) use ($difficulty) {
                $query->whereRaw('LOWER(difficulty) = ?', [$difficulty]);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString()
            ->through(function (Problem $problem) {
                return [
                    'problemId' => $problem->problemId,
                    'title' => $problem->title,
                    'description' => $problem->description,
                    'difficulty' => strtolower((string) $problem->difficulty),
                    'language' => $problem->language,
                    'status' => $problem->status,
                    'creatorName' => $problem->creator?->username,
                    'createdAt' => $problem->created_at->diffForHumans(null, false, false, 2),
                    'visibility'=>$problem->visibility,
                ];
            });

        return Inertia::render('ProblemBrowsing', [
            'problems' => $problems,
            'currentUser'=> auth()->user()?->username,
            'filters' => [
                'search' => $search,
                'difficulty' => $difficulty,
            ],
        ]);
    }

    public function show(Problem $problem)
    {
        $problem->load('creator:userId,username');
        $discussion= $problem->discussion()->with('member:userId,username')->latest()->get();
        $testCases = $problem->testCases()->get(['input', 'expected_output']);
        $submissions = $problem->submissions()->with('member:userId,username')->where('userId', auth()->id())->latest()->get();
        $sharedSolutions = $problem->sharedSolutions()->with(['member:userId,username', 'submission:submissionId,language'])->latest()->get();
        return Inertia::render('ProblemDetail', [
            'problem' => [
                'problemId' => $problem->problemId,
                'title' => $problem->title,
                'description' => $problem->description,
                'difficulty' => strtolower((string) $problem->difficulty),
                'status' => $problem->status,
                'creatorId' => $problem->creatorId,
                'creatorName' => $problem->creator?->username,
                'createdAt' => $problem->created_at->diffForHumans(null, false, false, 2),
                'testCases' => $testCases,
                'submissions' => $submissions->map(function ($submission) {
                    return [
                        'submissionId' => $submission->submissionId,
                        'username' => $submission->member?->username,
                        'language' => $submission->language,
                        'code' => $submission->code,
                        'status' => $submission->status,
                        'stdout' => $submission->stdout,
                        'stderr' => $submission->stderr,
                        'compileOutput' => $submission->compile_output,
                        'createdAt' => $submission->created_at->diffForHumans(null, false, false, 2),
                    ];
                }),
                'sharedSolutions' => $sharedSolutions->map(function ($solution) {
                    return [
                        'solutionId' => $solution->solutionId,
                        'title' => $solution->title,
                        'username' => $solution->member?->username ?? 'Unknown user',
                        'language' => $solution->submission?->language ?? 'Unknown language',
                        'code' => $solution->code,
                        'likes' => $solution->likes()->count(),
                        'isLikedByCurrentUser' => $solution->likes()->where('userId', auth()->id())->exists(),
                        'explanation' => $solution->explanation,
                        'createdAt' => $solution->created_at->diffForHumans(null, false, false, 2),
                    ];
                }),
                'discussions' => $discussion->map(function ($discussion) {
                    return [
                        'discussionId' => $discussion->discussionId,
                        'username' => $discussion->member?->username ?? 'Unknown user',
                        'title' => $discussion->title ?? 'Discussion',
                        'content' => $discussion->content,
                        'likes' => $discussion->likes()->count(),
                        'isLikedByCurrentUser' => $discussion->likes()->where('userId', auth()->id())->exists(),
                        'createdAt' => $discussion->created_at->diffForHumans(null, false, false, 2),
                        'comments' => $discussion->comments()->with('member:userId,username')->latest()->get()->map(function ($comment) {
                            return [
                                'likes' => $comment->likes()->count(),
                                'isLikedByCurrentUser' => $comment->likes()->where('userId', auth()->id())->exists()    ,
                                'commentId' => $comment->commentId,
                                'username' => $comment->member?->username ?? 'Unknown user',
                                'content' => $comment->content,
                                'createdAt' => $comment->created_at->diffForHumans(null, false, false, 2),
                            ];
                        })
                    ];
                }),
            ],
        ]);
    }
}
