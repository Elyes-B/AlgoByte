<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discussion;
use App\Models\Comment;

class DiscussionController extends Controller
{

    public function store(Request $request, $problemId)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $discussion = Discussion::create([
            'problemId' => $problemId,
            'userId' => auth()->id(),
            'title'=>   $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Discussion created successfully',
            'data' => [
                'discussionId' => $discussion->discussionId,
                'problemId' => $problemId,
                'userId' => auth()->id(),
                'username' => $request->user()?->username ?? '',
                'title' => $validated['title'],
                'content' => $validated['content'],
                'likes' => 0,
                'comments' => [],
                'createdAt' => $discussion->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    public function storeComment(Request $request, $discussionId)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = Comment::create([
            'discussionId' => $discussionId,
            'userId' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Comment created successfully',
            'data' => [
                'commentId' => $comment->commentId,
                'discussionId' => $discussionId,
                'userId' => auth()->id(),
                'username' => $request->user()?->username ?? '',
                'content' => $validated['content'],
                'likes' => 0,
                'createdAt' => $comment->created_at->toDateTimeString(),
            ],
        ], 201);
    }
}
