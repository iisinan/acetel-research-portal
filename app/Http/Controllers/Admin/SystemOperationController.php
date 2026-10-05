<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SystemOperationController extends Controller
{
    public function index()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();
        $failedJobs = DB::table('failed_jobs')->latest('failed_at')->take(10)->get();

        // Basic storage estimation (for demonstration in a standard Laravel setup)
        $storageDisk = config('filesystems.default');
        
        return view('admin.operations.index', compact('pendingJobs', 'failedJobsCount', 'failedJobs', 'storageDisk'));
    }

    public function retryJob($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [(string)$id]]);
            return back()->with('success', "Job {$id} has been queued for retry.");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to retry job: " . $e->getMessage());
        }
    }

    public function flushFailedJobs()
    {
        try {
            Artisan::call('queue:flush');
            return back()->with('success', 'All failed jobs have been cleared.');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to flush jobs: " . $e->getMessage());
        }
    }
}
