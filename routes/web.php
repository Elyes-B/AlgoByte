<?php

use App\Http\Controllers\Admin\AdminProblemController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiscussionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\Piston\PistonExecutionService;
use App\Http\Controllers\ProblemCreationController;
use App\Http\Controllers\ProblemBrowsingController;
use App\Http\Controllers\CodeSubmissionController;
use App\Http\Controllers\CodeSolutionController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/editor', function () {
    return Inertia::render('Editor');
})->middleware(['auth', 'verified'])->name('editor');

Route::get('/problemcreation', function () {
    return Inertia::render('problemCreation');
})->middleware(['auth', 'verified'])->name('problem-creation.index');

Route::post('/problemcreation', [ProblemCreationController::class, 'store'])->middleware(['auth', 'verified'])->name('problem-creation.store');

Route::post('/discussions/{discussion}/upvote', [DiscussionController::class, 'upvote'])->middleware(['auth', 'verified'])->name('discussions.upvote');
Route::post('/discussions/{discussion}/downvote', [DiscussionController::class, 'downvote'])->middleware(['auth', 'verified'])->name('discussions.downvote');

Route::post('/comments/{comment}/upvote', [DiscussionController::class, 'upvoteComment'])->middleware(['auth', 'verified'])->name('comments.upvote');
Route::post('/comments/{comment}/downvote', [DiscussionController::class, 'downvoteComment'])->middleware(['auth', 'verified'])->name('comments.downvote');

Route::get('/browse-problems', [ProblemBrowsingController::class, 'index'])->middleware(['auth', 'verified'])->name('browse-problems.index');

Route::post('/reports', [ReportController::class, 'store'])->middleware(['auth', 'verified'])->name('reports.store');

Route::get('/browse-problems/{problem}', [ProblemBrowsingController::class, 'show'])->middleware(['auth', 'verified'])->name('browse-problems.show');

Route::post('/browse-problems/{problem}/submission', [CodeSubmissionController::class, 'store'])->middleware(['auth', 'verified'])->name('submissions.store');
Route::post('/browse-problems/{problem}/solution', [CodeSolutionController::class, 'store'])->middleware(['auth', 'verified'])->name('solutions.store');
Route::post('/execute', function (Request $request, PistonExecutionService $piston) {
    $validated = $request->validate([
        'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
        'code' => ['required', 'string', 'max:100000'],
    ]);

    $result = $piston->execute($validated['language'], $validated['code']);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => $result->message,
            'data' => [
                'status' => $result->status,
                'stdout' => $result->stdout,
                'stderr' => $result->stderr,
                'compile_output' => $result->compileOutput,
                'exit_code' => $result->exitCode,
                'execution_time_ms' => $result->executionTimeMs,
                'runtime' => $result->runtime,
                'runtime_version' => $result->runtimeVersion,
                'signal' => $result->signal,
            ],
        ]);
    }

    return back()->with([
        'executionResult' => $result->stdout ?? $result->stderr ?? $result->compileOutput ?? $result->message,
    ]);
})->middleware(['auth', 'verified'])->name('execute');

Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // users can view other users profiles, but they can't edit them, only the owner of the profile can edit it
    Route::get('/users/{username}', [ProfileController::class, 'show'])->name('profile.show');

    // if the user owns the account the can use the following crud methods
    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //if the user has is_admin to true they can access the admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // shows all the pending problems that the admin can either accept or reject
        Route::get('/problems', [AdminProblemController::class, 'index'])->name('problems.index');

        // updates the status of the problem to either accepted or rejected
        Route::patch('/problems/{problem}/status', [AdminProblemController::class, 'updateStatus'])->name('problems.updateStatus');
    });
    // user can dismiss a notification by deleting it, this will remove the notification from the database and it will not be shown to the user again
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
});


Route::get('/users/{username}/history', [HistoryController::class, 'renderHistoryPage'])
    ->name('history.index');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

require __DIR__.'/auth.php';
