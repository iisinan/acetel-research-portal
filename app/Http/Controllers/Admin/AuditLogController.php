<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->latest()->paginate(20);
        return view('admin.audit.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.audit.show', compact('auditLog'));
    }
}
