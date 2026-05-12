<?php

use App\Http\Controllers\Admin\AdminProblemController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\Piston\PistonExecutionService;

//main page router ridrects the user to the home page if they are authenticated, otherwise to the welcome page(which is login page)
//all routes that end with the middle mean that the user must be authenticated and verified to access them, otherwise they will be redirected to the login page
Route::get('/', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

//Editor page
Route::get('/editor', function () {
    return Inertia::render('Editor');
})->middleware(['auth', 'verified'])->name('editor');

//after we press the run button it goes over the execute route and executes the code using the PistonExecutionService and returns the result to the user
Route::post('/execute', function (Request $request, PistonExecutionService $piston) {

    //checks for the most important info which is the language and the code
    $validated = $request->validate([
        'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
        'code' => ['required', 'string', 'max:100000'],
    ]);

    //execute the code using the PistonExecutionService execute method and get the result
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

    //returns the result of the execution to the user, it checks if the following attributes are not null
    return back()->with([
        'executionResult' => $result->stdout ?? $result->stderr ?? $result->compileOutput ?? $result->message,
    ]);
})->middleware(['auth', 'verified'])->name('execute');

//the welcome page which is the login page, it checks if the user can login or register
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

//main index page of the site
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

require __DIR__.'/auth.php';
