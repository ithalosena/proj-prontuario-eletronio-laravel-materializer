<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs      = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        $totalLogs = AuditLog::count();
        return view('content.pages.listagem_audit_logs', compact('logs', 'totalLogs'));
    }
}
