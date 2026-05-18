<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Problem;
use App\Models\Report;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Stats
        $stats = [
            'totalUsers' => Member::count(),
            'totalProblems' => Problem::count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
            'totalReports' => Report::count(),
        ];

        // Recent reports
        $recentReports = Report::with([
                'reporter',
                'problem'
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->reportId,
                    'reporter' => $report->reporter?->username ?? 'Unknown',
                    'problem' => $report->problem?->title ?? 'Unknown',
                    'reason' => $report->reason,
                    'severity' => $report->severity,
                    'status' => $report->status,
                    'createdAt' => optional($report->created_at)?->diffForHumans(),
                ];
            });

            Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentReports' => $recentReports,
        ]);

            return response()->json([
            'message' => 'Problem created successfully.',
            ]
        , 201);




    }
}
