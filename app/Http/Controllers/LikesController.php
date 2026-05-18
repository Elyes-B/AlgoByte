<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Discussion;
use App\Models\SharedSolution;
use Illuminate\Http\Request;

class LikesController extends Controller
{
    //Solution likes
    public function upvoteSolution(Request $request, SharedSolution $solution)
    {
        $userId = auth()->id();
        if($solution->likes()->where('userId', $userId)->exists()) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $solution->likes()->create(['userId' => $userId,]);
        return response()->json(['message' => 'Upvoted successfully']);
    }
    public function downvoteSolution(Request $request, SharedSolution $solution)
    {
        $userId = auth()->id();
        $like = $solution->likes()->where('userId', $userId)->first();
        if($like) {
            $like->delete();
        }
        return response()->json(['message' => 'Downvoted successfully']);
    }

    //

    //Discussion likes
public function upvote(Request $request, Discussion $discussion)
    {
        $userId = auth()->id();
        if($discussion->likes()->where('userId', $userId)->exists()) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $discussion->likes()->create(['userId' => $userId]);
        return response()->json(['message' => 'Upvoted successfully']);
    }
    public function downvote(Request $request, Discussion $discussion)
    {
        $userId = auth()->id();
        $like = $discussion->likes()->where('userId', $userId)->first();
        if($like) {
            $like->delete();
        }
        return response()->json(['message' => 'Downvoted successfully']);
    }
//
    public function upvoteComment(Request $request, Comment $comment)
    {
        $userId = auth()->id();
        if($comment->likes()->where('userId', $userId)->exists()) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $comment->likes()->create(['userId' => $userId]);
        return response()->json(['message' => 'Upvoted successfully']);
    }

    public function downvoteComment(Request $request, Comment $comment)
    {
        $userId = auth()->id();
        $like = $comment->likes()->where('userId', $userId)->first();
        if($like) {
            $like->delete();
        }
        return response()->json(['message' => 'Downvoted successfully']);
    }




   }
