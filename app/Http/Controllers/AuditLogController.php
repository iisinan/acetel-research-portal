<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index()
    {
        // Authorization check
        if (!Auth::user()->hasRole(['Admin', 'Director'])) {
             abort(403, 'Unauthorized access to audit logs.');
        }

        $logs = AuditLog::with('user')->latest()->paginate(20);

        return view('admin.audit_logs.index', compact('logs'));
    }
}
