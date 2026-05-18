<?php

namespace App\Providers;

use App\Models\Problem;
use App\Models\Report;
use App\Observers\ProblemObserver;
use App\Observers\ReportObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Problem::observe(ProblemObserver::class);
        Report::observe(ReportObserver::class);
    }
}
