<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\ThesisProject;
use App\Models\StudentMilestone;
use App\Models\Submission;
use App\Models\DefenceEvent;
use App\Observers\AuditObserver;

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
        if (app()->runningInConsole()) {
            @ini_set('max_execution_time', '0');
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }
        } else {
            @ini_set('max_execution_time', '120');
            @ini_set('default_socket_timeout', '120');
        }

        User::observe(AuditObserver::class);
        ThesisProject::observe(AuditObserver::class);
        ThesisProject::observe(\App\Observers\ThesisProjectObserver::class);
        StudentMilestone::observe(AuditObserver::class);
        Submission::observe(AuditObserver::class);
        DefenceEvent::observe(AuditObserver::class);
        
        \Illuminate\Support\Facades\Gate::policy(DefenceEvent::class, \App\Policies\DefenceEventPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(ThesisProject::class, \App\Policies\ThesisProjectPolicy::class);
        
        if (app()->environment('production') || config('app.force_https') || env('FORCE_HTTPS', false) || str_starts_with(config('app.url', env('APP_URL', '')), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
