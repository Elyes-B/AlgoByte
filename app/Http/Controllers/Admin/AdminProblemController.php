<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminProblemController extends Controller
{
    // displays the main page of the admin panel where reports await review
    public function index(Request $request)
    {
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action. Admins only.');
        }

        $reports = Report::with(['reporter', 'problem'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/ProblemReview', [
            'reports' => $reports
        ]);
    }

    public function updateStatus(Request $request, Report $report)
    {
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'action' => 'required|in:approved,rejected'
        ]);

        $action = $request->action;
        $report->status = $action;
        $report->reviewerId = $request->user()->userId;
        $report->save();

        $problemTitle = $report->problem?->title ?? 'the reported problem';
        $reporterId = $report->reporterId;

        if ($action === 'approved' && $report->problem) {
            $problem = $report->problem;
            $problem->delete();

            Notification::create([
                'user_id' => $reporterId,
                'title' => 'Report Approved',
                'message' => "Your report for '{$problemTitle}' was approved and the problem was removed.",
                'type' => 'success',
                'link' => null,
            ]);
        } else {

            Notification::create([
                'user_id' => $reporterId,
                'title' => 'Report Reviewed',
                'message' => "Your report for '{$problemTitle}' was reviewed and the problem was kept.",
                'type' => 'info',
                'link' => "/problems/{$report->problem?->problemId}",
            ]);
        }

        return back();
    }
}
