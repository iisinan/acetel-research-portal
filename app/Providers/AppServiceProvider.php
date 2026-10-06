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
        @ini_set('max_execution_time', '180');
        @ini_set('default_socket_timeout', '180');
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        User::observe(AuditObserver::class);
        ThesisProject::observe(AuditObserver::class);
        ThesisProject::observe(\App\Observers\ThesisProjectObserver::class);
        StudentMilestone::observe(AuditObserver::class);
        Submission::observe(AuditObserver::class);
        DefenceEvent::observe(AuditObserver::class);
        
        \Illuminate\Support\Facades\Gate::policy(DefenceEvent::class, \App\Policies\DefenceEventPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(ThesisProject::class, \App\Policies\ThesisProjectPolicy::class);
        
        if(env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
