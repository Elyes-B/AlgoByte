<?php

namespace App\Http\Controllers;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'problemId' => ['required', 'integer', 'exists:problems,problemId'],
            'reason' => ['required', 'string', 'max:1000'],
            'severity' => ['required', 'string', 'in:low,medium,high'],
            'status' => ['required', 'string', 'in:pending,reviewed,rejected'],
        ]);

        Report::create([
            'reporterId' => Auth::id(),
            'reviewerId' => null,
            'problemId' => $validated['problemId'],
            'reason' => $validated['reason'],
            'severity' => $validated['severity'],
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Report submitted successfully.'], 201);
    }
}
