<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // UX-06 (v0.10.1): filtros por ação, usuário, entidade e intervalo de datas (GET preservável)
        $filtroAction = $request->input('action');
        $filtroUser   = $request->input('user_id');
        $filtroModel  = $request->input('model_type');
        $dataDe       = $request->input('data_de');
        $dataAte      = $request->input('data_ate');

        $logs = AuditLog::with('user')
            ->when($filtroAction, fn($q) => $q->where('action', $filtroAction))
            ->when($filtroUser,   fn($q) => $q->where('user_id', $filtroUser))
            ->when($filtroModel,  fn($q) => $q->where('model_type', $filtroModel))
            ->when($dataDe,  fn($q) => $q->whereDate('created_at', '>=', $dataDe))
            ->when($dataAte, fn($q) => $q->whereDate('created_at', '<=', $dataAte))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalLogs = AuditLog::count();

        // Opções dos selects de filtro — distintos do próprio audit_logs
        $acoes     = AuditLog::distinct()->orderBy('action')->pluck('action');
        $entidades = AuditLog::whereNotNull('model_type')->distinct()->orderBy('model_type')->pluck('model_type');
        $usuarios  = User::whereIn('id', AuditLog::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('name')->get();

        return view('content.pages.listagem_audit_logs', compact(
            'logs', 'totalLogs', 'acoes', 'entidades', 'usuarios',
            'filtroAction', 'filtroUser', 'filtroModel', 'dataDe', 'dataAte'
        ));
    }
}
